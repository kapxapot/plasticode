<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository extends IdiormRepository implements TagRepositoryInterface
{
    /**
     * @inheritDoc
     */
    protected function entityClass() : string
    {
        return Tag::class;
    }

    public function store(array $data) : Tag
    {
        return $this->storeEntity($data);
    }

    public function getIdsByTag(string $entityType, string $tag) : ScalarCollection
    {
        return $this
            ->entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->scalarize('entity_id');
    }

    public function getAllByTag(string $tag) : TagCollection
    {
        return TagCollection::from(
            $this->byTagQuery($tag)
        );
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

    public function search(string $searchQuery) : TagCollection
    {
        return TagCollection::from(
            $this
                ->query()
                ->search($searchQuery, '(tag like ?)')
                ->orderByAsc('tag')
        );
    }

    // queries

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

    protected function byEntityQuery(string $entityType, int $entityId) : Query
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId);
    }
}
