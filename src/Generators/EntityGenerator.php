<?php

namespace Plasticode\Generators;

use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Strings;
use Plasticode\Validation\ValidationRules;

class EntityGenerator extends Contained {
	protected $entity;
	protected $rules;
	protected $taggable;
	protected $tagsField = 'tags';

	public function __construct($container, $entity) {
		parent::__construct($container);
		
		$this->entity = $entity;
		$this->rules = new ValidationRules($container);
	}
	
	protected function rule($name, $optional = false) {
		return $this->rules->get($name, $optional);
	}
	
	protected function optional($name) {
		return $this->rule($name, true);
	}

	protected function getRules($data, $id = null) {
		return [];
	}
	
	protected function getOptions() {
		return [];
	}
	
	public function afterLoad($item) {
		return $item;
	}
	
	public function beforeSave($data, $id = null) {
		return $data;
	}
	
	public function afterSave($item, $data) {
	}
	
	public function afterDelete($item) {
	}
	
	public function getAdminParams($args) {
		$settings = $this->getSettings();
		$params = $settings['entities'][$this->entity];
		
		$params['base'] = $this->router->pathFor('admin.index');

		return $params;
	}
	
	public function updateTags($item) {
		if ($this->taggable) {
			$tags = Strings::toTags($item->{$this->tagsField});
	
			$this->db->saveTags($this->taggable, $item->id, $tags);
		}
	}

	public function deleteTags($item) {
		if ($this->taggable) {
			$this->db->deleteTags($this->taggable, $item->id);
		}
	}
	
	public function validate($request, $data, $id = null) {
		$rules = $this->getRules($data, $id);
		$validation = $this->validator->validate($request, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}
	}
	
	public function generateAPIRoutes($app, $access) {
		$this->generateGetAllRoute($app, $access);
		
		$api = $this->getSettings("tables.{$this->entity}.api");
		
		if ($api == "full") {
			$this->generateCRUDRoutes($app, $access);
		}
		
		return $this;
	}
	
	public function generateGetAllRoute($app, $access) {
		$alias = $this->entity;
		$provider = $this;
		$options = $this->getOptions();

		$uri = $options['uri'] ?? $alias;

		$app->get('/' . $uri, function($request, $response, $args) use ($alias, $provider, $options) {
			$options['args'] = $args;
			$options['params'] = $request->getParams();
			
			return $this->db->jsonMany($response, $alias, $provider, $options);
		})->add($access($alias, 'api_read'));
		
		return $this;
	}
	
	public function generateCRUDRoutes($app, $access) {
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

	public function generateAdminPageRoute($app, $access) {
		$alias = $this->entity;
		$provider = $this;
		$options = $this->getOptions();

		$uri = $options['admin_uri'] ?? $options['uri'] ?? $alias;

		$app->get('/' . $uri, function($request, $response, $args) use ($provider, $options) {
			$templateName = isset($options['admin_template'])
				? ('entities/' . $options['admin_template'])
				: 'entity';

			$params = $provider->getAdminParams($args);
			
			$params['create_onload'] = $request->getQueryParam('create', false);
			$params['edit_onload'] = intval($request->getQueryParam('edit', 0));

			return $this->view->render($response, 'admin/' . $templateName . '.twig', $params);
		})->add($access($alias, 'read_own', 'admin.index'))->setName('admin.entities.' . $alias);
		
		return $this;
	}
}
