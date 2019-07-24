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
    
    private function getTableName(string $table) : string
    {
        $tableSettings = $this->getTableSettings($table);
        return $tableSettings['table'] ?? $table;
    }
    
    public function forTable(string $table) : \ORM
    {
        $tableName = $this->getTableName($table);
        
        return \ORM::forTable($tableName);
    }
    
    private function fields(string $table) : ?array
    {
        $tableSettings = $this->getTableSettings($table);
        return $tableSettings['fields'] ?? null;
    }
    
    private function hasField(string $table, string $field) : bool
    {
        $tableSettings = $this->getTableSettings($table);
        $has = $tableSettings['fields'] ?? null;

        return $has && in_array($field, $has);
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
    
    public function filterBy($items, string $field, array $args) : \ORM
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
    
    private function enrichRights($table, $item)
    {
        if (is_null($item)) {
            return null;
        }
        
        $tr = $this->getTableRights($table);
        return $tr->enrichRights($item);
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
    
    /**
     * Returns entity as array enriched with access rights
     *
     * @param string $table
     * @param mixed $id
     * @return array
     */
    public function get(string $table, $id) : array
    {
        $obj = $this->getObj($table, $id);
        $item = $obj ? $obj->asArray() : null;
        
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

    public function isPublished($item) : bool
    {
        return isset($item['published_at']) && Date::happened($item['published_at']);
    }
    
    /**
     * Creates record and fills it with data
     *
     * @param string $table
     * @param array $data
     * @return \ORM
     */
    public function create(string $table, array $data = null) : \ORM
    {
        $item = $this->forTable($table)->create();
        
        if ($data) {
            $item->set($data);
        }
        
        return $item;
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
    
    public function getQueryCount()
    {
        return \ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', [ 'Questions' ])
            ->findOne()['Value'];
    }
}
