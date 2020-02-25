<?php

namespace Plasticode\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository implements TagRepositoryInterface
{
    private static function entityQuery(string $entityType) : Query
    {
        return self::query()
            ->where('entity_type', $entityType);
    }
    
    public static function getByEntity(string $entityType, $entityId) : Query
    {
        return self::entityQuery($entityType)
            ->where('entity_id', $entityId);
    }

    public static function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return self::entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    public static function deleteByEntity(string $entityType, $entityId) : bool
    {
        return self::entityQuery($entityType)
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

    private function byTagQuery(string $tag) : Query
    {
        return Tag::query()
            ->where('tag', $tag);
    }
    
    public static function search(string $searchQuery) : Collection
    {
        return self::query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }
}
