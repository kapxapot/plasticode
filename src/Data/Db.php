<?php

namespace Plasticode\Data;

use Plasticode\Contained;
use Plasticode\Util\Date;

final class Db extends Contained
{
    /**
     * Tables settings
     *
     * @var array
     */
    private $tables;

    public function getTableSettings(string $table) : ?array
    {
        if (is_null($this->tables)) {
            $this->tables = $this->getSettings('tables');
        }

        return $this->tables[$table] ?? null;
    }
    
    protected function getTableName(string $table) : string
    {
        $tableSettings = $this->getTableSettings($table);
        return $tableSettings['table'] ?? $table;
    }
    
    public function forTable(string $table) : \ORM
    {
        $tableName = $this->getTableName($table);
        
        return \ORM::forTable($tableName);
    }
    
    public function fields(string $table) : ?array
    {
        $tableSettings = $this->getTableSettings($table);
        return $tableSettings['fields'] ?? null;
    }
    
    public function hasField(string $table, string $field) : bool
    {
        $fields = $this->fields($table);
        return $fields && in_array($field, $fields);
    }
    
    public function selectMany(string $table, array $exclude = null) : \ORM
    {
        $t = $this->forTable($table);
        $fields = $this->fields($table);
        
        if (!is_null($fields) && !is_null($exclude)) {
            $fields = array_diff($fields, $exclude);
        }

        return !is_null($fields)
            ? $t->selectMany($fields)
            : $t->selectMany();
    }
    
    protected function filterBy($items, string $field, array $args) : \ORM
    {
        return $items->where($field, $args['id']);
    }
    
    public function getEntityById(string $table, $id)
    {
        $path = "data.{$table}.{$id}";
        $value = $this->cache->get($path);

        if (is_null($value)) {
            $entities = $this->forTable($table)
                ->findArray();
            
            foreach ($entities as $entity) {
                $this->cache->set("data.{$table}.{$entity['id']}", $entity);
            }
        }

        return $this->cache->get($path);
    }
    
    public function getTableRights(string $table) : TableRights
    {
        return new TableRights($this->container, $table);
    }
    
    public function can($table, $rights, $item = null)
    {
        $access = $this->getRights($table, $item);
        return $access[$rights];
    }
    
    public function getRights($table, $item = null)
    {
        $tableRights = $this->getTableRights($table);
        return $tableRights->get($item);
    }
    
    protected function enrichRights($table, $item)
    {
        if (is_null($item)) {
            return null;
        }
        
        $tr = $this->getTableRights($table);
        return $tr->enrichRights($item);
    }
    
    protected function enrichRightsMany($table, $items)
    {
        if (is_null($items)) {
            return null;
        }
        
        $tr = $this->getTableRights($table);

        return array_values(array_map(array($tr, 'enrichRights'), $items));
    }
    
    /**
     * Returns new updated_at value for the table, if it has the corresponding field
     *
     * @param string $table
     * @return string|null
     */
    public function updatedAt(string $table) : ?string
    {
        return $this->hasField($table, 'updated_at')
            ? Date::dbNow()
            : null;
    }

    /**
     * Updated created_by / updated_by fields if applicable
     *
     * @param string $table
     * @param array $data
     * @param mixed $userId
     * @return array
     */
    public function stampBy(string $table, array $data, $userId) : array
    {
        $createdBy = $data['created_by'] ?? null;

        if ($this->hasField($table, 'created_by') && is_null($createdBy)) {
            $data['created_by'] = $userId;
        }
        
        if ($this->hasField($table, 'updated_by')) {
            $data['updated_by'] = $userId;
        }

        return $data;
    }
    
    // SHORTCUTS
    private function asArray($obj) : ?array
    {
        return $obj ? $obj->asArray() : null;
    }
    
    protected function get($table, $id)
    {
        $obj = $this->getObj($table, $id);
        $item = $this->asArray($obj);
        
        return $this->enrichRights($table, $item);
    }
    
    public function getObj($table, $id, $where = null)
    {
        $query = $this
            ->forTable($table)
            ->where('id', $id);
            
        if ($where) {
            $query = $where($query);
        }
        
        return $query->findOne();
    }
    
    protected function getBy($table, $where)
    {
        $obj = $this->getObjBy($table, $where);
        $item = $this->asArray($obj);
        
        return $this->enrichRights($table, $item);
    }
    
    public function getObjBy($table, $where)
    {
        $query = $this->forTable($table);
        return $where($query)->findOne();
    }

    public function isPublished($item) : bool
    {
        return isset($item['published_at']) && Date::happened($item['published_at']);
    }
    
    private function getManyBaseQuery(string $table, $where = null)
    {
        $query = $this->forTable($table);

        if ($where) {
            $query = $where($query);
        }
        
        return $query;
    }
    
    protected function getArray($query) : ?array
    {
        $result = $query->findArray();
        return $result ? array_values($result) : null;
    }
    
    public function getMany($table, $where = null)
    {
        $query = $this->getManyBaseQuery($table, $where);
        $items = $this->getArray($query);
        
        return $this->enrichRightsMany($table, $items);
    }

    public function getManyObj($table, $where = null)
    {
        return $this
            ->getManyBaseQuery($table, $where)
            ->findMany();
    }
    
    protected function getManyByField($table, $field, $value)
    {
        return $this->getMany($table, function ($q) use ($field, $value) {
            return $q->where($field, $value);
        });
    }
    
    public function getManyObjByField($table, $field, $value, $where = null)
    {
        return $this->getManyObj($table, function ($q) use ($field, $value, $where) {
            $q = $q->where($field, $value);
            
            if ($where) {
                $q = $where($q);
            }
            
            return $q;
        });
    }
    
    public function getCount($table, $where = null)
    {
        return $this->getManyBaseQuery($table, $where)->count();
    }
    
    public function getObjByField($table, $field, $value, $where = null)
    {
        $query = $this
            ->forTable($table)
            ->where($field, $value);
            
        if ($where) {
            $query = $where($query);
        }

        return $query->findOne();
    }
    
    protected function getByField($table, $field, $value, $where = null)
    {
        $obj = $this->getObjByField($table, $field, $value, $where);
        $item = $this->asArray($obj);
        
        return $this->enrichRights($table, $item);
    }
    
    protected function getIdByField($table, $field, $value, $where = null)
    {
        $obj = $this->getObjByField($table, $field, $value, $where);
        return $obj ? $obj->id : null;
    }
    
    protected function getIdByName($table, $name, $where = null)
    {
        return $this->getIdByField($table, 'name', $name, $where);
    }
    
    protected function setFieldNoStamps($table, $id, $field, $value)
    {
        return $this->setField($table, $id, $field, $value, false);
    }
    
    protected function setField($table, $id, $field, $value, $withStamps = true)
    {
        if (strlen($id) == 0) {
            throw new \Exception("No id provided for {$table}.{$field} set.");
        }
        
        return $this->set($table, $id, [ $field => $value ], $withStamps);
    }
    
    public function create(string $table, array $data = null)
    {
        $item = $this->forTable($table)->create();
        
        if ($data) {
            $item->set($data);
        }
        
        return $item;
    }
    
    protected function set(string $table, $id, array $data, bool $withStamps = true)
    {
        $obj = $this->getObj($table, $id);
        
        if (!$obj) {
            $obj = $this->create($table);
            $obj->id = $id;
        } elseif ($withStamps) {
            $upd = $this->updatedAt($table);
            if ($upd) {
                $obj->updated_at = $upd;
            }
        }

        $obj->set($data);
        $obj->save();
        
        $item = $this->asArray($obj);
        
        return $this->enrichRights($table, $item);
    }
    
    public function isRecursiveParent(string $table, $id, $parentId, string $parentField = null) : bool
    {
        $recursive = false;

        while ($parentId != null) {
            if ($id == $parentId) {
                $recursive = true;
                break;
            }
            
            $parent = $this->get($table, $parentId);

            if (!$parent) {
                break;
            }
            
            $parentId = $parent[$parentField ?? 'parent_id'];
        }

        return $recursive;
    }
    
    public function deleteBy($table, \Closure $where)
    {
        $q = $this->forTable($table);
        $q = $where($q);
        
        $q->deleteMany();
    }
    
    public function getQueryCount()
    {
        return \ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', [ 'Questions' ])
            ->findOne()['Value'];
    }
}
