<?php

namespace Plasticode\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository extends IdiormRepository implements TagRepositoryInterface
{
    // public function getByEntity(string $entityType, int $entityId) : Query
    // {
    //     return $this->entityQuery($entityType)
    //         ->where('entity_id', $entityId);
    // }

    public function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return $this->entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    public function deleteByEntity(string $entityType, int $entityId) : bool
    {
        return $this->entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }
    
    public function getByTag(string $tag) : Collection
    {
        return $this->byTagQuery($tag)->all();
    }

    public function exists(string $tag) : bool
    {
        return $this->byTagQuery($tag)->any();
    }

    private function entityQuery(string $entityType) : Query
    {
        return Tag::query()
            ->where('entity_type', $entityType);
    }

    private function byTagQuery(string $tag) : Query
    {
        return Tag::query()
            ->where('tag', $tag);
    }

    public function search(string $searchQuery) : Collection
    {
        return Tag::query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }
}
