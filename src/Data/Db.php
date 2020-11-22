<?php

namespace Plasticode\Data;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Date;

/**
 * Db layer via Idiorm (\ORM).
 */
final class Db
{
    private SettingsProviderInterface $settingsProvider;

    /**
     * Tables settings
     */
    private ?array $tables = null;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

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

    public function hasField(string $table, string $field) : bool
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

    /**
     * Returns new updated_at value for the table,
     * if it has the corresponding field.
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
     * @param mixed $userId
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
     */
    public function create(string $table, array $data = null) : \ORM
    {
        $item = $this->forTable($table)->create();
        
        if ($data) {
            $item->set($data);
        }
        
        return $item;
    }

    public function getQueryCount() : int
    {
        return \ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', ['Questions'])
            ->findOne()['Value'];
    }
}
