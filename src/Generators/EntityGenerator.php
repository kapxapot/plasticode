<?php

namespace Plasticode\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationRules;

class EntityGenerator extends Contained
{
	protected $entity;
	protected $rules;

	public function __construct($container, $entity)
	{
		parent::__construct($container);
		
		$this->entity = $entity;
		$this->rules = new ValidationRules($container);
	}
	
	protected function rule($name, $optional = false)
	{
		return $this->rules->get($name, $optional);
	}
	
	protected function optional($name)
	{
		return $this->rule($name, true);
	}

	protected function getRules($data, $id = null)
	{
		return [
			'updated_at' => v::unchanged($this->entity, $id)
		];
	}
	
	public function validate($request, $data, $id = null)
	{
		$rules = $this->getRules($data, $id);
		$validation = $this->validator->validate($request, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}
	}
	
	protected function getOptions()
	{
		return [];
	}
	
	public function afterLoad($item)
	{
	    $item['type'] = $this->entity;
	    
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
	
	public function getAdminParams($args)
	{
		$settings = $this->getSettings();
		$params = $settings['entities'][$this->entity];
		
		$params['base'] = $this->router->pathFor('admin.index');

		return $params;
	}

	public function generateAPIRoutes($app, $access)
	{
		$this->generateGetAllRoute($app, $access);
		
		$api = $this->getSettings("tables.{$this->entity}.api");
		
		if ($api == "full") {
			$this->generateCRUDRoutes($app, $access);
		}
		
		return $this;
	}
	
	public function generateGetAllRoute($app, $access)
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

		//$uri = $options['uri'] ?? $alias;
		
		foreach ($endPoints as $endPoint) {
		    $endPointUri = $endPoint['uri'];
		    $endPointOptions = $endPoint['options'];

    		$app->get('/' . $endPointUri, function($request, $response, $args) use ($alias, $provider, $endPointOptions) {
    			$endPointOptions['args'] = $args;
    			$endPointOptions['params'] = $request->getParams();
    			
    			return $this->db->jsonMany($response, $alias, $provider, $endPointOptions);
    		})->add($access($alias, 'api_read'));
		}
		
		return $this;
	}
	
	public function generateCRUDRoutes($app, $access)
	{
		$alias = $this->entity;
		$table = $this->entity;
		$provider = $this;
		
		$shortPath = '/' . $alias;
		$fullPath = '/' . $alias . '/{id:\d+}';

		$get = $app->get($fullPath, function ($request, $response, $args) use ($table, $provider) {
			return $this->db->apiGet($response, $table, $args['id'], $provider);
		});
		
		$post = $app->post($shortPath, function ($request, $response, $args) use ($table, $provider) {
			return $this->db->apiCreate($request, $response, $table, $provider);
		});
		
		$put = $app->put($fullPath, function ($request, $response, $args) use ($table, $provider) {
			return $this->db->apiUpdate($request, $response, $table, $args['id'], $provider);
		});
		
		$delete = $app->delete($fullPath, function ($request, $response, $args) use ($table, $provider) {
			return $this->db->apiDelete($response, $table, $args['id'], $provider);
		});
		
		if ($access) {
			$get->add($access($table, 'api_read'));
			$post->add($access($table, 'api_create'));
			$put->add($access($table, 'api_edit'));
			$delete->add($access($table, 'api_delete'));
		}
		
		return $this;
	}

	public function generateAdminPageRoute($app, $access)
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

			return $this->view->render($response, 'admin/' . $templateName . '.twig', $params);
		})->add($access($alias, 'read_own', 'admin.index'))->setName('admin.entities.' . $alias);
		
		return $this;
	}
}
