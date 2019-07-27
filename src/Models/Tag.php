<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\SearchableInterface;

class Tag extends DbModel implements LinkableInterface, SearchableInterface
{
    // queries
    
    private static function entityQuery($entityType) : Query
    {
        return self::query()
            ->where('entity_type', $entityType);
    }
    
    public static function getByEntity($entityType, $entityId) : Query
    {
        return self::entityQuery($entityType)
            ->where('entity_id', $entityId);
    }

    // getters
    
    public static function getIdsByTag($entityType, $tag) : Collection
    {
        return self::entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    // ops
    
    public static function deleteByEntity($entityType, $entityId)
    {
        return self::entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }
    
    // interfaces
    
    public function url()
    {
        return self::$linker->tag($this->tag);
    }
    
    public static function search($searchQuery) : Collection
    {
        return self::query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }
    
    public function serialize()
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
