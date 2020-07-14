<?php

namespace Plasticode\Generators;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Data\Api;
use Plasticode\Data\Rights;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator;
use Slim\App;
use Slim\Http\Request as SlimRequest;
use Slim\Interfaces\RouterInterface;

class EntityGenerator
{
    protected ContainerInterface $container;

    protected SettingsProviderInterface $settingsProvider;
    protected RouterInterface $router;
    protected Api $api;

    protected ValidatorInterface $validator;
    protected ValidationRules $rules;

    /**
     * Entity name.
     */
    protected string $entity;

    /**
     * Id field.
     * 
     * 'id' by default. Override it if the id field is different.
     */
    protected string $idField = 'id';

    public function __construct(
        ContainerInterface $container,
        string $entity
    )
    {
        $this->container = $container;

        $this->settingsProvider = $container->settingsProvider;
        $this->router = $container->router;
        $this->api = $container->api;

        $this->validator = $container->validator;
        $this->rules = $container->validationRules;

        $this->entity = $entity;
    }

    /**
     * Get entity name.
     */
    public function getEntity() : string
    {
        return $this->entity;
    }

    protected function rule(string $name, bool $optional = false) : Validator
    {
        return $this->rules->get($name, $optional);
    }

    protected function optional(string $name) : Validator
    {
        return $this->rule($name, true);
    }

    protected function getRules(array $data, $id = null) : array
    {
        return [
            'updated_at' => Validator::unchanged($this->entity, $id),
        ];
    }

    public function validate(
        SlimRequest $request,
        array $data,
        $id = null
    ) : void
    {
        $rules = $this->getRules($data, $id);

        $this
            ->validator
            ->validateRequest($request, $rules)
            ->throwOnFail();
    }

    protected function getOptions() : array
    {
        return [];
    }

    public function afterLoad(array $item) : array
    {
        return $item;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        return $data;
    }

    public function afterSave(array $item, array $data) : void
    {
    }

    public function afterDelete(array $item) : void
    {
    }

    public function getAdminParams(array $args) : array
    {
        $params = $this->settingsProvider
            ->get('entities.' . $this->entity);

        $params['base'] = $this->router->pathFor('admin.index');

        return $params;
    }

    /**
     * Generate API routes based on settings.
     *
     * @param \Closure $access Creates {@see \Plasticode\Middleware\AccessMiddleware}
     */
    public function generateAPIRoutes(App $app, \Closure $access) : void
    {
        $api = $this->settingsProvider
            ->get('tables.' . $this->entity . '.api');

        if (strlen($api) == 0) {
            return;
        }

        // supported modes:
        //
        // full: CRUD + get all
        // read: get one + get all
        // read_one: get one

        if ($api == 'full' || $api == 'read') {
            $this->generateGetAllRoute($app, $access);
        }

        $this->generateCRUDRoutes($app, $access, $api == 'full');
    }

    /**
     * Generate API route for loading all entities.
     *
     * @param \Closure $access Creates {@see \Plasticode\Middleware\AccessMiddleware}
     */
    public function generateGetAllRoute(App $app, \Closure $access) : void
    {
        $provider = $this;

        $options = $this->getOptions();
        $noFilterOptions = $options;

        if (isset($noFilterOptions['filter'])) {
            unset($noFilterOptions['filter']);
        }

        $endPoints[] = [
            'uri' => $this->entity,
            'options' => $noFilterOptions,
        ];

        if (isset($options['uri'])) {
            $endPoints[] = [
                'uri' => $options['uri'],
                'options' => $options,
            ];
        }

        foreach ($endPoints as $endPoint) {
            $endPointUri = $endPoint['uri'];
            $endPointOptions = $endPoint['options'];

            $app
                ->get(
                    '/' . $endPointUri,
                    function ($request, $response, $args) use ($provider, $endPointOptions) {
                        $endPointOptions['args'] = $args;
                        $endPointOptions['params'] = $request->getParams();

                        return $this->api->getMany(
                            $response, $provider, $endPointOptions
                        );
                    }
                )
                ->add(
                    $access($this->entity, Rights::API_READ)
                );
        }
    }

    /**
     * Generate CRUD routes.
     *
     * @param \Closure $access Creates {@see \Plasticode\Middleware\AccessMiddleware}
     */
    public function generateCRUDRoutes(App $app, \Closure $access, bool $isFullApi) : void
    {
        $provider = $this;

        $shortPath = '/' . $this->entity;
        $fullPath = '/' . $this->entity . '/{id:\d+}';

        $get = $app->get(
            $fullPath,
            function ($request, $response, $args) use ($provider) {
                return $this->api->get(
                    $response, $args['id'], $provider
                );
            }
        );

        if ($access) {
            $get->add(
                $access($this->entity, Rights::API_READ)
            );
        }

        if (!$isFullApi) {
            return; // that's all, folks
        }

        $post = $app->post(
            $shortPath,
            function ($request, $response) use ($provider) {
                return $this->api->create(
                    $request, $response, $provider
                );
            }
        );

        $put = $app->put(
            $fullPath,
            function ($request, $response, $args) use ($provider) {
                return $this->api->update(
                    $request, $response, $args['id'], $provider
                );
            }
        );

        $delete = $app->delete(
            $fullPath,
            function ($request, $response, $args) use ($provider) {
                return $this->api->delete(
                    $response, $args['id'], $provider
                );
            }
        );

        if ($access) {
            $post->add(
                $access($this->entity, Rights::API_CREATE)
            );

            $put->add(
                $access($this->entity, Rights::API_EDIT)
            );

            $delete->add(
                $access($this->entity, Rights::API_DELETE)
            );
        }
    }

    /**
     * Generate admin page route.
     *
     * @param \Closure $access Creates {@see \Plasticode\Middleware\AccessMiddleware}
     */
    public function generateAdminPageRoute(App $app, \Closure $access) : void
    {
        $options = $this->getOptions();
        $provider = $this;

        $uri = $options['admin_uri'] ?? $options['uri'] ?? $this->entity;
        $adminArgs = $options['admin_args'] ?? null;

        $route = $app->get(
            '/' . $uri,
            function ($request, $response, $args) use ($provider, $options, $adminArgs) {
                $templateName = isset($options['admin_template'])
                    ? ('entities/' . $options['admin_template'])
                    : 'entity';

                $params = $provider->getAdminParams($args);

                $action = $request->getQueryParam('action', null);
                $id = $request->getQueryParam('id', null);

                if ($action) {
                    $params['action_onload'] = [
                        'action' => $action,
                        'id' => $id,
                    ];
                }

                if ($adminArgs) {
                    $params['args'] = $adminArgs;
                }

                // $this = container
                return $this->view->render(
                    $response, 'admin/' . $templateName . '.twig', $params
                );
            }
        );

        $route->add($access($this->entity, Rights::READ_OWN, 'admin.index'));
        $route->setName('admin.entities.' . $this->entity);
    }
}
