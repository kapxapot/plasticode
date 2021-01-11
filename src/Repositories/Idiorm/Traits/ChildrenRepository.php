<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Data\Query;

trait ChildrenRepository
{
    protected string $parentIdField = 'parent_id';

    /**
     * Adds filter by parent to a query.
     */
    protected function filterByParent(Query $query, ?int $parentId): Query
    {
        return $query->where($this->parentIdField, $parentId);
    }

    /**
     * Adds filter by no parent to a query.
     */
    protected function filterOrphans(Query $query): Query
    {
        return $query->whereNull($this->parentIdField);
    }
}
