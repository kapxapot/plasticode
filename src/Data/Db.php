<?php

namespace Plasticode\Data;

use ORM;
use Plasticode\Util\Date;

/**
 * Db layer via Idiorm ({@see ORM}).
 */
final class Db
{
    private DbMetadata $metadata;

    public function __construct(
        DbMetadata $metadata
    )
    {
        $this->metadata = $metadata;
    }

    public function metadata() : DbMetadata
    {
        return $this->metadata;
    }

    public function selectMany(string $tableAlias, array $exclude = null) : ORM
    {
        $t = $this->forTable($tableAlias);
        $fields = $this->metadata->fields($tableAlias);

        if ($fields && $exclude) {
            $fields = array_diff($fields, $exclude);
        }

        return $fields
            ? $t->selectMany($fields)
            : $t->selectMany();
    }

    public function filterBy(ORM $items, string $field, array $args) : ORM
    {
        return $items->where($field, $args['id']);
    }

    /**
     * Returns new updated_at value for the table,
     * if it has the corresponding field.
     */
    public function updatedAt(string $tableAlias) : ?string
    {
        return $this->metadata->hasField($tableAlias, 'updated_at')
            ? Date::dbNow()
            : null;
    }

    /**
     * Updated created_by / updated_by fields if applicable.
     *
     * @param mixed $userId
     */
    public function stampBy(string $tableAlias, array $data, $userId) : array
    {
        $createdBy = $data['created_by'] ?? null;

        if ($this->hasField($tableAlias, 'created_by') && is_null($createdBy)) {
            $data['created_by'] = $userId;
        }

        if ($this->hasField($tableAlias, 'updated_by')) {
            $data['updated_by'] = $userId;
        }

        return $data;
    }

    public function getObj(string $tableAlias, $id, ?callable $where = null) : ORM
    {
        $query = $this
            ->forTable($tableAlias)
            ->where('id', $id);

        if ($where) {
            $query = $where($query);
        }

        return $query->findOne();
    }

    /**
     * Creates record and fills it with data.
     */
    public function create(string $tableAlias, array $data = null) : ORM
    {
        return $this->forTable($tableAlias)->create($data);
    }

    private function forTable(string $tableAlias) : ORM
    {
        $tableName = $this->metadata->tableName($tableAlias);

        return ORM::forTable($tableName);
    }

    public function getQueryCount() : int
    {
        return ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', ['Questions'])
            ->findOne()['Value'];
    }
}
