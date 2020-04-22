<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Collection;
use Plasticode\Models\Interfaces\ChildrenInterface;
use Plasticode\Query;

trait ChildrenRepository
{
    protected string $parentIdField = 'parent_id';

    protected abstract function query() : Query;
    protected abstract function get(?int $id) : ?ChildrenInterface;

    /**
     * Get entities by parent.
     */
    public function getByParent(?int $parentId) : Collection
    {
        return $this->query()
            ->where($this->parentIdField, $parentId)
            ->all();
    }

    protected function withParent(
        ChildrenInterface $entity
    ) : ChildrenInterface
    {
        return $entity->withParent(
            $this->get($entity->parentId())
        );
    }

    protected function withChildren(
        ChildrenInterface $entity
    ) : ChildrenInterface
    {
        return $entity->withChildren(
            $this->getByParent($entity->getId())
        );
    }

    /**
     * Get entities without parent.
     */
    public function getOrphans() : Collection
    {
        return $this->orphansQuery()->all();
    }

    protected function orphansQuery(?Query $query = null) : Query
    {
        $query ??= $this->query();

        return $query->whereNull($this->parentIdField);
    }
}
