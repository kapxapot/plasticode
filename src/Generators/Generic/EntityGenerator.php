<?php

namespace Plasticode\Generators\Generic;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Config;
use Plasticode\Controllers\Admin\AdminPageControllerFactory;
use Plasticode\Core\Request;
use Plasticode\Core\Response;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Data\Rights;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Search\SearchParams;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\EntityRelated;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Slim\App;
use Slim\Interfaces\RouterInterface;

abstract class EntityGenerator implements EntityGeneratorInterface
{
    use EntityRelated;

    protected Access $access;
    protected AccessMiddlewareFactory $accessMiddlewareFactory;
    protected ApiInterface $api;
    protected AuthInterface $auth;
    protected Config $config;
    protected RouterInterface $router;
    protected SettingsProviderInterface $settingsProvider;
    protected ValidationRules $rules;
    protected ValidatorInterface $validator;

    public function __construct(
        GeneratorContext $context
    )
    {
        $this->access = $context->access();
        $this->accessMiddlewareFactory = $context->accessMiddlewareFactory();
        $this->api = $context->api();
        $this->auth = $context->auth();
        $this->config = $context->config();
        $this->router = $context->router();
        $this->settingsProvider = $context->settingsProvider();
        $this->rules = $context->validationRules();
        $this->validator = $context->validator();
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
        ServerRequestInterface $request,
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
            ->get($this->getSettingsAlias() . '.api');

        if (strlen($api) == 0) {
            return;
        }

        // supported modes:
        //
        // full: CRUD + get all
        // read: get one + get all
        // read_all: get all
        // read_one: get one

        if ($api !== 'read_one') {
            $this->generateGetAllRoute($app);
        }

        if ($api !== 'read_all') {
            $this->generateCRUDRoutes($app, $api === 'full');
        }
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

        $endPoints = [];

        $noDefaultUri = $options['no_default_uri'] ?? false;

        if ($noDefaultUri !== true) {
            $endPoints[] = [
                'uri' => $this->getEntity(),
                'options' => $noFilterOptions,
            ];
        }

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
                    fn ($request, $response, $args) =>
                        $generator->getSearchResult($request, $response)
                        ?? $api->getMany(
                            $request,
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

    protected function getSearchResult(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ?ResponseInterface
    {
        if (!Request::isDataTables($request)) {
            return null;
        }

        $repo = $this->getRepository();

        if (!$repo instanceof FilteringRepositoryInterface) {
            // search is not supported
            return null;
        }

        $searchParams = SearchParams::fromRequest($request);

        $searchResult = $repo->getSearchResult($searchParams);

        // we need to apply the access rights here
        // for that we are going to serialize it first,
        // then aply the access rights
        // and send it with the response

        $searchResultSerialized = $searchResult->toArray();

        $searchResultSerialized['data'] = array_map(
            fn (array $item) => $this->enrichRights($item),
            $searchResultSerialized['data']
        );

        return Response::json($response, $searchResultSerialized);
    }

    protected function enrichRights(array $data): array
    {
        $rights = $this->access->getTableRights(
            $this->getEntity(),
            $this->auth->getUser()
        );

        return $rights->enrichRights($data);
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
        )->setName('admin.entities.' . $this->getSettingsAlias());
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
            ->get($this->getSettingsAlias());

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

    protected function getSettingsAlias(): string
    {
        return $this->getEntity();
    }
}
