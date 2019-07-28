<?php

namespace Plasticode\Generators;

use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Slim\App;

class EntityGenerator extends Contained
{
    protected $entity;
    protected $rules;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container);
        
        $this->entity = $entity;
        $this->rules = new ValidationRules($container);
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
    
    public function validate(ServerRequestInterface $request, array $data, $id = null) : void
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
    
    public function afterLoad($item)
    {
        return $item;
    }
    
    public function beforeSave($data, $id = null)
    {
        return $data;
    }
    
    public function afterSave($item, $data)
    {
    }
    
    public function afterDelete($item)
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
        
        $api = $this->getSettings("tables.{$this->entity}.api");
        
        if ($api == "full") {
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
        $alias = $this->entity;
        $provider = $this;
        
        $options = $this->getOptions();
        
        $noFilterOptions = $options;
        if (isset($noFilterOptions['filter'])) {
            unset($noFilterOptions['filter']);
        }

        $endPoints[] = [ 'uri' => $alias, 'options' => $noFilterOptions ];
        
        if (isset($options['uri'])) {
            $endPoints[] = [ 'uri' => $options['uri'], 'options' => $options ];
        }
        
        foreach ($endPoints as $endPoint) {
            $endPointUri = $endPoint['uri'];
            $endPointOptions = $endPoint['options'];

            $app->get('/' . $endPointUri, function($request, $response, $args) use ($alias, $provider, $endPointOptions) {
                $endPointOptions['args'] = $args;
                $endPointOptions['params'] = $request->getParams();
                
                return $this->api->getMany($response, $alias, $provider, $endPointOptions);
            })->add($access($alias, 'api_read'));
        }
    }
    
    /**
     * Generate CRUD routes
     *
     * @param App $app
     * @param \Closure $access
     * @return void
     */
    public function generateCRUDRoutes(App $app, \Closure $access)
    {
        $alias = $this->entity;
        $table = $this->entity;
        $provider = $this;
        
        $shortPath = '/' . $alias;
        $fullPath = '/' . $alias . '/{id:\d+}';

        $get = $app->get(
            $fullPath,
            function ($request, $response, $args) use ($table, $provider) {
                return $this->api->get(
                    $response,
                    $table,
                    $args['id'],
                    $provider
                );
            }
        );
        
        $post = $app->post(
            $shortPath,
            function ($request, $response, $args) use ($table, $provider) {
                return $this->api->create(
                    $request,
                    $response,
                    $table,
                    $provider
                );
            }
        );
        
        $put = $app->put(
            $fullPath,
            function ($request, $response, $args) use ($table, $provider) {
                return $this->api->update(
                    $request,
                    $response,
                    $table,
                    $args['id'],
                    $provider
                );
            }
        );
        
        $delete = $app->delete(
            $fullPath,
            function ($request, $response, $args) use ($table, $provider) {
                return $this->api->delete(
                    $response,
                    $table,
                    $args['id'],
                    $provider
                );
            }
        );
        
        if ($access) {
            $get->add($access($table, 'api_read'));
            $post->add($access($table, 'api_create'));
            $put->add($access($table, 'api_edit'));
            $delete->add($access($table, 'api_delete'));
        }
        
        return $this;
    }

    /**
     * Generate admin page route
     *
     * @param App $app
     * @param \Closure $access
     * @return void
     */
    public function generateAdminPageRoute(App $app, \Closure $access)
    {
        $alias = $this->entity;
        $provider = $this;
        $options = $this->getOptions();

        $uri = $options['admin_uri'] ?? $options['uri'] ?? $alias;
        $adminArgs = $options['admin_args'] ?? null;

        $app->get('/' . $uri, function($request, $response, $args) use ($provider, $options, $adminArgs) {
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
                $response,
                'admin/' . $templateName . '.twig',
                $params
            );
        })
            ->add($access($alias, 'read_own', 'admin.index'))
            ->setName('admin.entities.' . $alias);
        
        return $this;
    }
}
