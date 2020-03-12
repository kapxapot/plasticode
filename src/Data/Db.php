<?php

namespace Plasticode\Data;

use Plasticode\Auth\Access;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Date;

/**
 * Db layer via Idiorm (\ORM).
 */
final class Db
{
    /** @var Access */
    private $access;

    /** @var CacheInterface */
    private $cache;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        Access $access,
        CacheInterface $cache,
        SettingsProviderInterface $settingsProvider,
        UserRepositoryInterface $userRepository
    )
    {
        $this->access = $access;
        $this->cache = $cache;
        $this->settingsProvider = $settingsProvider;
        $this->userRepository = $userRepository;
    }

    /**
     * Tables settings
     *
     * @var array
     */
    private $tables;

    public function getTableSettings(string $table) : ?array
    {
        if (is_null($this->tables)) {
            $this->tables = $this->settingsProvider->get('tables');
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
        $has = $tableSettings['has'] ?? null;

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
    
    public function getEntityById(string $table, $id) : array
    {
        $path = 'data.' . $table . '.' . $id;
        $value = $this->cache->get($path);

        if (is_null($value)) {
            $entities = $this
                ->forTable($table)
                ->findArray();
            
            foreach ($entities as $entity) {
                $this->cache->set(
                    'data.' . $table . '.' . $entity['id'],
                    $entity
                );
            }
        }

        return $this->cache->get($path);
    }
    
    public function getTableRights(string $table) : Rights
    {
        return $this->access->getAllRights($table);
    }
    
    public function enrichRights(string $table, array $item) : array
    {
        return $this
            ->getTableRights($table)
            ->enrichRights($item);
    }
    
    /**
     * Returns new updated_at value for the table,
     * if it has the corresponding field
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
     * Adds user names for created_by / updated_by
     *
     * @param string $table
     * @param array $item
     * @return array
     */
    public function addUserNames(string $table, array $item) : array
    {
        if ($this->hasField($table, 'created_by')) {
            $creator = '[no data]';

            if (isset($item['created_by'])) {
                $created = $this->userRepository->get($item['created_by']);
                $creator = $created->login ?? $item['created_by'];
            }
    
            $item['created_by_name'] = $creator;
        }

        if ($this->hasField($table, 'updated_by')) {
            $updater = '[no data]';

            if (isset($item['updated_by'])) {
                $updated = $this->userRepository->get($item['updated_by']);
                $updater = $updated->login ?? $item['updated_by'];
            }
    
            $item['updated_by_name'] = $updater;
        }
        
        return $item;
    }
    
    public function getObj(string $table, $id, \Closure $where = null) : \ORM
    {
        $query = $this
            ->forTable($table)
            ->where('id', $id);
            
        if ($where) {
            $query = $where($query);
        }
        
        return $query->findOne();
    }

    public function isPublished(array $item) : bool
    {
        return isset($item['published_at'])
            && Date::happened($item['published_at']);
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
    
    public function isRecursiveParent(
        string $table, $id, $parentId, string $parentField = null
    ) : bool
    {
        $parentField = $parentField ?? 'parent_id';
        $recursive = false;

        while ($parentId != null) {
            if ($id == $parentId) {
                $recursive = true;
                break;
            }
            
            $parent = $this->getObj($table, $parentId);

            if (!$parent) {
                break;
            }
            
            $parentId = $parent[$parentField];
        }

        return $recursive;
    }
    
    public function getQueryCount() : int
    {
        return \ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', [ 'Questions' ])
            ->findOne()['Value'];
    }
}
