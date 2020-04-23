<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\SearchableRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository extends IdiormRepository implements TagRepositoryInterface, SearchableRepositoryInterface
{
    protected string $entityClass = Tag::class;

    public function store(array $data) : Tag
    {
        return $this->storeEntity($data);
    }

    public function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return $this
            ->entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    public function getAllByTag(string $tag) : Collection
    {
        return $this->byTagQuery($tag)->all();
    }

    public function exists(string $tag) : bool
    {
        return $this->byTagQuery($tag)->any();
    }

    public function deleteByEntity(string $entityType, int $entityId) : bool
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }

    protected function entityQuery(string $entityType) : Query
    {
        return $this
            ->query()
            ->where('entity_type', $entityType);
    }

    protected function byTagQuery(string $tag) : Query
    {
        return $this
            ->query()
            ->where('tag', $tag);
    }

    public function search(string $searchQuery) : Collection
    {
        return $this
            ->query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }

    protected function byEntityQuery(string $entityType, int $entityId) : Query
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId);
    }
}
