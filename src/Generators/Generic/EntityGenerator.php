<?php

namespace Plasticode\Generators\Generic;

use Plasticode\Config\Config;
use Plasticode\Controllers\Admin\AdminPageControllerFactory;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Data\Rights;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\EntityRelated;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Slim\App;
use Slim\Http\Request;
use Slim\Interfaces\RouterInterface;

abstract class EntityGenerator implements EntityGeneratorInterface
{
    use EntityRelated;

    protected SettingsProviderInterface $settingsProvider;
    protected Config $config;
    protected RouterInterface $router;
    protected ApiInterface $api;
    protected ValidatorInterface $validator;
    protected ValidationRules $rules;
    protected AccessMiddlewareFactory $accessMiddlewareFactory;

    public function __construct(
        GeneratorContext $context
    )
    {
        $this->settingsProvider = $context->settingsProvider();
        $this->config = $context->config();
        $this->router = $context->router();
        $this->api = $context->api();
        $this->validator = $context->validator();
        $this->rules = $context->validationRules();
        $this->accessMiddlewareFactory = $context->accessMiddlewareFactory();
    }

    public function getEntity(): string
    {
        return $this->pluralAlias();
    }

    protected function rule(string $name, bool $optional = false): Validator
    {
        return $this->rules->get($name, $optional);
    }

    protected function optional(string $name): Validator
    {
        return $this->rule($name, true);
    }

    protected function getRules(array $data, $id = null): array
    {
        return [];
    }

    public function validate(
        Request $request,
        array $data,
        $id = null
    ): void
    {
        $rules = $this->getRules($data, $id);

        $this
            ->validator
            ->validateRequest($request, $rules)
            ->throwOnFail();
    }

    public function getOptions(): array
    {
        return [];
    }

    public function afterLoad(array $item): array
    {
        return $item;
    }

    public function beforeSave(array $data, $id = null): array
    {
        return $data;
    }

    public function afterSave(array $item, array $data): void
    {
    }

    public function afterDelete(array $item): void
    {
    }

    public function generateAPIRoutes(App $app): void
    {
        $api = $this
            ->config
            ->tableMetadata()
            ->get($this->getEntity() . '.api');

        if (strlen($api) == 0) {
            return;
        }

        // supported modes:
        //
        // full: CRUD + get all
        // read: get one + get all
        // read_one: get one

        if ($api == 'full' || $api == 'read') {
            $this->generateGetAllRoute($app);
        }

        $this->generateCRUDRoutes($app, $api == 'full');
    }

    /**
     * Generate API route for loading all entities.
     */
    private function generateGetAllRoute(App $app): void
    {
        $generator = $this;
        $api = $this->api;
        $options = $this->getOptions();

        $noFilterOptions = $options;

        if (isset($noFilterOptions['filter'])) {
            unset($noFilterOptions['filter']);
        }

        $endPoints[] = [
            'uri' => $this->getEntity(),
            'options' => $noFilterOptions,
        ];

        if (isset($options['uri'])) {
            $endPoints[] = [
                'uri' => $options['uri'],
                'options' => $options,
            ];
        }

        foreach ($endPoints as $endPoint) {
            $app
                ->get(
                    '/' . $endPoint['uri'],
                    fn ($request, $response, $args) => $api->getMany(
                        $response,
                        $generator,
                        array_merge(
                            $endPoint['options'],
                            [
                                'args' => $args,
                                'params' => $request->getParams(),
                            ]
                        )
                    )
                )
                ->add(
                    $this->accessMiddlewareFactory->make(
                        $this->getEntity(),
                        Rights::API_READ
                    )
                );
        }
    }

    /**
     * Generate CRUD routes.
     */
    private function generateCRUDRoutes(App $app, bool $isFullApi): void
    {
        $generator = $this;
        $api = $this->api;

        $shortPath = '/' . $this->getEntity();
        $fullPath = '/' . $this->getEntity() . '/{id:\d+}';

        $app->get(
            $fullPath,
            fn ($request, $response, $args) => $api->get(
                $response,
                $args['id'],
                $generator
            )
        )->add(
            $this->accessMiddlewareFactory->make(
                $this->getEntity(),
                Rights::API_READ
            )
        );

        if (!$isFullApi) {
            // that's all, folks
            return;
        }

        $app->post(
            $shortPath,
            fn ($request, $response) => $api->create(
                $request,
                $response,
                $generator
            )
        )->add(
            $this->accessMiddlewareFactory->make(
                $this->getEntity(),
                Rights::API_CREATE
            )
        );

        $app->put(
            $fullPath,
            fn ($request, $response, $args) => $api->update(
                $request,
                $response,
                $args['id'],
                $generator
            )
        )->add(
            $this->accessMiddlewareFactory->make(
                $this->getEntity(),
                Rights::API_EDIT
            )
        );

        $app->delete(
            $fullPath,
            fn ($request, $response, $args) => $api->delete(
                $response,
                $args['id'],
                $generator
            )
        )->add(
            $this->accessMiddlewareFactory->make(
                $this->getEntity(),
                Rights::API_DELETE
            )
        );
    }

    public function generateAdminPageRoute(App $app): void
    {
        $container = $app->getContainer();
        $generator = $this;

        $app->get(
            '/' . $this->getAdminPageUri(),
            function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args
            ) use ($container, $generator) {
                /** @var AdminPageControllerFactory $factory */
                $factory = $container->get(AdminPageControllerFactory::class);
                $controller = ($factory)($generator);

                return ($controller)($request, $response, $args);
            }
        )->add(
            $this->accessMiddlewareFactory->make(
                $this->getEntity(),
                Rights::READ_OWN,
                'admin.index'
            )
        )->setName('admin.entities.' . $this->getEntity());
    }

    public function getAdminPageUri(): string
    {
        $options = $this->getOptions();

        return $options['admin_uri'] ?? $options['uri'] ?? $this->getEntity();
    }

    /**
     * Returns params for the view.
     */
    public function getAdminParams(array $args): array
    {
        $params = $this
            ->config
            ->entitySettings()
            ->get($this->getEntity());

        $params['base'] = $this->router->pathFor('admin.index');

        return $params;
    }

    public function getAdminArgs(): ?array
    {
        return $this->getOptions()['admin_args'] ?? null;
    }

    public function getAdminTemplateName(): string
    {
        $options = $this->getOptions();

        return isset($options['admin_template'])
            ? 'entities/' . $options['admin_template']
            : 'entity';
    }
}
