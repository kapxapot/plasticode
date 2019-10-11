<?php

namespace Plasticode\Generators;

use Plasticode\Contained;
use Plasticode\Data\Rights;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Slim\App;

class EntityGenerator extends Contained
{
    /**
     * Entity name
     *
     * @var string
     */
    protected $entity;

    /**
     * Validation rules
     *
     * @var Plasticode\Validation\ValidationRules
     */
    protected $rules;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container);
        
        $this->entity = $entity;
        $this->rules = new ValidationRules($container);
    }

    /**
     * Get entity name
     *
     * @return string
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
        ServerRequestInterface $request,
        array $data,
        $id = null
    ) : void
    {
        $rules = $this->getRules($data, $id);
        $validation = $this->validator->validateRequest($request, $rules);
        
        if ($validation->failed()) {
            throw new ValidationException($validation->errors);
        }
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
        $settings = $this->getSettings();
        $params = $settings['entities'][$this->entity];
        
        $params['base'] = $this->router->pathFor('admin.index');

        return $params;
    }

    /**
     * Generate API routes based on settings
     *
     * @param App $app
     * @param \Closure $access Creates AccessMiddleware
     * @return void
     */
    public function generateAPIRoutes(App $app, \Closure $access) : void
    {
        $this->generateGetAllRoute($app, $access);
        
        $api = $this->getSettings('tables.' . $this->entity . '.api');
        
        if ($api == 'full') {
            $this->generateCRUDRoutes($app, $access);
        }
    }
    
    /**
     * Generate API route for loading all entities
     *
     * @param App $app
     * @param \Closure $access Creates AccessMiddleware
     * @return void
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

            $app->get(
                '/' . $endPointUri,
                function ($request, $response, $args) use ($provider, $endPointOptions) {
                    $endPointOptions['args'] = $args;
                    $endPointOptions['params'] = $request->getParams();
                
                    return $this->api->getMany(
                        $response, $provider, $endPointOptions
                    );
                }
            )->add($access($this->entity, Rights::API_READ));
        }
    }
    
    /**
     * Generate CRUD routes
     *
     * @param App $app
     * @param \Closure $access
     * @return void
     */
    public function generateCRUDRoutes(App $app, \Closure $access) : void
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
            $get->add(
                $access($this->entity, Rights::API_READ)
            );

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
     * Generate admin page route
     *
     * @param App $app
     * @param \Closure $access
     * @return void
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

                return $this->view->render(
                    $response, 'admin/' . $templateName . '.twig', $params
                );
            }
        );
        
        $route->add($access($this->entity, Rights::READ_OWN, 'admin.index'));
        $route->setName('admin.entities.' . $this->entity);
    }
}
