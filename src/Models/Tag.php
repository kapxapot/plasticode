<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Interfaces\SerializableInterface;

class Tag extends DbModel implements LinkableInterface, SearchableInterface, SerializableInterface
{
    // queries
    
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

    // getters
    
    public static function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return self::entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    // ops
    
    public static function deleteByEntity(string $entityType, $entityId) : bool
    {
        return self::entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }

    public static function exists(string $tag) : bool
    {
        return self::query()
            ->where('tag', $tag)
            ->any();
    }
    
    // interfaces
    
    public function url() : ?string
    {
        return self::$linker->tag($this->tag);
    }
    
    public static function search(string $searchQuery) : Collection
    {
        return self::query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }
    
    public function serialize() : ?array
    {
        return [
            'id' => $this->getId(),
            'tag' => $this->tag,
        ];
    }
    
    public function code() : string
    {
        $parts = [
            "tag:{$this->tag}"
        ];
        
        $code = self::$parser->joinTagParts($parts);
        
        return "[[{$code}]]";
    }
}
