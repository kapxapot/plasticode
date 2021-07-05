<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Search\SearchParams;

class TagRepository extends IdiormRepository implements TagRepositoryInterface
{
    protected function entityClass(): string
    {
        return Tag::class;
    }

    public function store(array $data): Tag
    {
        return $this->storeEntity($data);
    }

    public function getIdsByTag(string $entityType, string $tag): NumericCollection
    {
        return $this
            ->entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->numerize('entity_id');
    }

    public function getAllByTag(string $tag): TagCollection
    {
        return TagCollection::from(
            $this->byTagQuery($tag)
        );
    }

    public function exists(string $tag): bool
    {
        return $this->byTagQuery($tag)->any();
    }

    public function deleteByEntity(string $entityType, int $entityId): bool
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }

    public function search(string $query): TagCollection
    {
        return TagCollection::from(
            $this
                ->query()
                ->search($query, '(tag like ?)')
                ->orderByAsc('tag')
        );
    }

    // queries

    protected function entityQuery(string $entityType): Query
    {
        return $this
            ->query()
            ->where('entity_type', $entityType);
    }

    protected function byTagQuery(string $tag): Query
    {
        return $this
            ->query()
            ->where('tag', $tag);
    }

    protected function byEntityQuery(string $entityType, int $entityId): Query
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId);
    }
}
