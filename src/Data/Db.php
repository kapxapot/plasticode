<?php

namespace Plasticode\Data;

use Plasticode\Contained;
use Plasticode\Core\Core;
use Plasticode\Core\Security;
use Plasticode\Exceptions\NotFoundException;
use Plasticode\Exceptions\AuthorizationException;
use Plasticode\Util\Date;

class Db extends Contained {
	protected $tables;

	/**
	 * Creates new Db instance.
	 * 
	 * @param ContainerInterface $c Slim container
	 */
	public function __construct($c) {
		parent::__construct($c);
		
		$this->tables = $this->getSettings('tables');
	}

	protected function getTableName($table) {
		return $this->tables[$table]['table'];
	}
	
	public function forTable($table) {
		$tableName = $this->getTableName($table);
		
		return \ORM::forTable($tableName);
	}
	
	public function fields($table) {
		return isset($this->tables[$table]['fields'])
			? $this->tables[$table]['fields']
			: null;
	}
	
	public function hasField($table, $field) {
		$fields = $this->fields($table);
		return $fields && in_array($field, $fields);
	}

	public function selectMany($table, $exclude = null) {
		$t = $this->forTable($table);
		$fields = $this->fields($table);
		
		if ($fields !== null && is_array($exclude)) {
			$fields = array_diff($fields, $exclude);
		}
		
		return ($fields !== null)
			? $t->selectMany($fields)
			: $t->selectMany();
	}

	protected function filterBy($items, $field, $args) {
		return $items->where($field, $args['id']);
	}

	public function jsonMany($response, $table, $provider, $options = []) {
		if (!$this->can($table, 'api_read')) {
			$this->logger->info("Unauthorized read attempt on {$table}");

			throw new AuthorizationException;
		}
		
		$items = $this->apiGetMany($table, $provider, $options);
		
		$response = Core::json($response, $items, $options);

		return $response;
	}

	public function apiGet($response, $table, $id, $provider) {
		$e = $this->selectMany($table)->findOne($id);

		if (!$e) {
            throw new NotFoundException;
		}

		if (!$this->can($table, 'api_read', $e)) {
			$this->logger->info("Unauthorized read attempt on {$table}: {$e->id}");

			throw new AuthorizationException;
		}
		
		$e = $provider->afterLoad($e);

		return Core::json($response, $e);
	}

	public function apiCreate($request, $response, $table, $provider) {
		if (!$this->can($table, 'create')) {
			$this->logger->info("Unauthorized create attempt on {$table}");

			throw new AuthorizationException;
		}

		$original = $request->getParsedBody();
		$data = $this->beforeValidate($request, $table, $original);
		
		$provider->validate($request, $data);
		
		$data = $provider->beforeSave($data);

		$e = $this->forTable($table)->create();
		
		$e->set($data);
		$e->save();
		
		$provider->updateTags($e);
		$provider->afterSave($e, $original);

		$this->logger->info("Created {$table}: {$e->id}");
		
		return $this->apiGet($response, $table, $e->id, $provider)->withStatus(201);
	}
	
	public function apiUpdate($request, $response, $table, $id, $provider) {
		$e = $this->forTable($table)->findOne($id);

		if (!$e) {
            throw new NotFoundException;
		}

		if (!$this->can($table, 'edit', $e)) {
			$this->logger->info("Unauthorized edit attempt on {$table}: {$e->id}");

			throw new AuthorizationException;
		}

		$original = $request->getParsedBody();
		$data = $this->beforeValidate($request, $table, $original, $id);

		$provider->validate($request, $data, $id);
		
		$data = $provider->beforeSave($data, $id);

		$e->set($data);
		$e->save();
		
		$provider->updateTags($e);
		$provider->afterSave($e, $original);
		
		$this->logger->info("Updated {$table}: {$e->id}");
		
		$response = $this->apiGet($response, $table, $e->id, $provider);

		return $response;
	}
	
	public function apiDelete($response, $table, $id, $provider) {
		$e = $this->forTable($table)->findOne($id);
		
		if (!$e) {
            throw new NotFoundException;
		}

		if (!$this->can($table, 'delete', $e)) {
			$this->logger->info("Unauthorized delete attempt on {$table}: {$e->id}");

			throw new AuthorizationException;
		}

		$e->delete();
		
		$provider->deleteTags($e);
		$provider->afterDelete($e);

		$this->logger->info("Deleted {$table}: {$e->id}");
		
		$response = $response->withStatus(204);

		return $response;
	}

	public function getEntityById($table, $id) {
		$path = "data.{$table}.{$id}";
		$value = $this->cache->get($path);

		if ($value === null) {
			$entities = $this->forTable($table)
				->findArray();
			
			foreach ($entities as $entity) {
				$this->cache->set("data.{$table}.{$entity['id']}", $entity);
			}
		}

		return $this->cache->get($path);
	}
	
	private function getTableRights($table) {
		return new TableRights($this->container, $table);
	}
	
	protected function can($table, $rights, $item = null) {
		$tableRights = $this->getTableRights($table);
		$access = $tableRights->get($item);

		return $access[$rights];
	}
	
	protected function enrichRights($table, $item) {
		$tr = $this->getTableRights($table);
		return $tr->enrichRights($item);
	}
	
	protected function enrichRightsMany($table, $items) {
		if ($items === null) {
			return null;
		}
		
		$tr = $this->getTableRights($table);
		return array_values(array_map(array($tr, 'enrichRights'), $items));
	}

	private function addUserNames($item) {
		if (isset($item['created_by'])) {
			$created = $this->getUser($item['created_by']);
			if ($created !== null) {
				$item['created_by_name'] = $created['login'];
			}
		}

		if (isset($item['updated_by'])) {
			$updated = $this->getUser($item['updated_by']);
			if ($updated !== null) {
				$item['updated_by_name'] = $updated['login'];
			}
		}
		
		return $item;
	}

	public function apiGetMany($table, $provider, $options = []) {
		$exclude = $options['exclude'] ?? null;

		$items = $this->selectMany($table, $exclude);
		
		if (isset($options['filter'])) {
			$items = $this->filterBy($items, $options['filter'], $options['args']);
		}

		$settings = $this->tables[$table];
		
		if (isset($settings['sort'])) {
			$sortBy = $settings['sort'];
			$reverse = isset($settings['reverse']);
			$items = $reverse
				? $items->orderByDesc($sortBy)
				: $items->orderByAsc($sortBy);
		}
		
		$array = $items->findArray();
		
		$tableRights = $this->getTableRights($table);

		$array = array_filter($array, array($tableRights, 'canRead'));
		$array = array_map(array($provider, 'afterLoad'), $array);
		$array = array_map(array($this, 'addUserNames'), $array);
		$array = array_map(array($tableRights, 'enrichRights'), $array);

		return array_values($array);
	}

	protected function beforeValidate($request, $table, $data, $id = null) {
		// unset
		$canPublish = $this->can($table, 'publish');
		
		if (isset($data['published']) && !$canPublish) {
			unset($data['published']);
		}

		if (array_key_exists('password', $data)) {
			$password = $data['password'];
			if (strlen($password) > 0) {
				$data['password'] = Security::encodePassword($password);
			}
			else {
				unset($data['password']);
			}
		}

		// dirty
		/*if ($this->hasField($table, 'created_at') && !$id) {
			$data['created_at'] = Date::now();
		}*/

		$upd = $this->updatedAt($table);
		if ($upd) {
			$data['updated_at'] = $upd;
		}
		
		$user = $this->auth->getUser();
		if ($this->hasField($table, 'created_by') && !$id) {
			$data['created_by'] = $user->id;
		}
		
		if ($this->hasField($table, 'updated_by')) {
			$data['updated_by'] = $user->id;
		}

		return $data;
	}
	
	private function updatedAt($table) {
		return $this->hasField($table, 'updated_at')
			? Date::now()
			: null;
	}

	// SHORTCUTS
	private function asArray($obj) {
		return $obj ? $obj->asArray() : null;
	}

	protected function get($table, $id) {
		$obj = $this->getObj($table, $id);
		return $this->asArray($obj);
	}
	
	public function getObj($table, $id) {
		return $this
			->forTable($table)
			->where('id', $id)
			->findOne();
	}

	protected function getBy($table, $where) {
		$obj = $this->getObjBy($table, $where);
		return $this->asArray($obj);
	}
	
	protected function getObjBy($table, $where) {
		$query = $this->forTable($table);
		return $where($query)->findOne();
	}
	
	protected function getProtected($table, $id, $where = null) {
		$editor = $this->can($table, 'edit');
		
		$where = $where ?? function($q) use ($id) {
			return $q->where('id', $id);
		};

		$result = $this->getBy($table, function($q) use ($where, $editor) {
			$q = $where($q);

			if (!$editor) {
				$user = $this->auth->getUser();
				
				$published = "(published = 1 and published_at < now())";

				if ($user) {
					$q = $q->whereRaw("({$published} or created_by = ?)", [ $user->id ]);
				}
				else {
					$q = $q->whereRaw($published);
				}
			}
			
			return $q;
		});
		
		return $this->enrichRights($table, $result);
	}

	private function getManyBaseQuery($table, $where = null) {
		$query = $this->forTable($table);

		if ($where) {
			$query = $where($query);
		}
		
		return $query;
	}
	
	protected function getArray($query) {
		$result = $query->findArray();
		return $result ? array_values($result) : null;
	}
	
	protected function getMany($table, $where = null) {
		$query = $this
			->getManyBaseQuery($table, $where);
		
		return $this->getArray($query);
	}
	
	protected function getManyObj($table, $where = null) {
		return $this
			->getManyBaseQuery($table, $where)
			->findMany();
	}
	
	protected function getManyByField($table, $field, $value) {
		return $this->getMany($table, function($q) use ($field, $value) {
			return $q->where($field, $value);
		});
	}

	protected function getObjByField($table, $field, $value, $where = null) {
		$query = $this
			->forTable($table)
			->where($field, $value);
			
		if ($where) {
			$query = $where($query);
		}

		return $query->findOne();
	}

	protected function getByField($table, $field, $value, $where = null) {
		$obj = $this->getObjByField($table, $field, $value, $where);
		return $this->asArray($obj);
	}

	protected function getIdByField($table, $field, $value, $where = null) {	
		$obj = $this->getObjByField($table, $field, $value, $where);
		return $obj ? $obj->id : null;
	}
	
	protected function getIdByName($table, $name, $where = null) {
		return $this->getIdByField($table, 'name', $name, $where);
	}

	protected function setFieldNoStamps($table, $id, $field, $value) {
		return $this->set($table, $id, [ $field => $value ], false);
	}
	
	protected function setField($table, $id, $field, $value, $withStamps = true) {
		return $this->set($table, $id, [ $field => $value ], $withStamps);
	}
	
	protected function set($table, $id, $data, $withStamps = true) {
		$obj = $this->getObj($table, $id);
		
		if (!$obj) {
			$obj = $this->forTable($table)->create();
			$obj->id = $id;
		}
		elseif ($withStamps) {
			$upd = $this->updatedAt($table);
			if ($upd) {
				$obj->updated_at = $upd;
			}
		}

		$obj->set($data);
		$obj->save();
		
		return $this->asArray($obj);
	}
	
	// getters
	public function getUsers() {
		return $this->getMany(Tables::USERS);
	}
	
	public function getUser($id) {
		return $this->get(Tables::USERS, $id);
	}

	public function getMenus() {
		return $this->getMany(Tables::MENUS, function($q) {
			return $q
				->orderByAsc('position');
		});
	}
	
	public function getMenu($id) {
		return $this->get(Tables::MENUS, $id);
	}

	public function getMenuItems($menuId) {
		return $this->getMany(Tables::MENU_ITEMS, function($q) use ($menuId) {
			return $q
				->where('menu_id', $menuId)
				->orderByAsc('position');
		});
	}

	public function getReplaces() {
		return $this->getMany(Tables::REPLACES);
	}
}
