<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;

trait ChildrenRepository
{
    protected string $parentIdField = 'parent_id';

    /**
     * Get children by parent query.
     */
    protected function filterByParent(Query $query, ?int $parentId) : Query
    {
        return $query->where($this->parentIdField, $parentId);
    }

    /**
     * Get entities without parent query.
     */
    protected function filterOrphans(Query $query) : Query
    {
        return $query->whereNull($this->parentIdField);
    }
}
