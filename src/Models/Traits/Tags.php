<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Models\Tag;
use Plasticode\Models\TagLink;
use Plasticode\Util\Strings;

trait Tags
{
    protected static function getTagsEntityType() : string
    {
        return static::getTable();
    }
    
    /**
     * Returns tags as an array of TRIMMED strings.
     */
    public function getTags() : array
    {
        $tags = $this->{static::$tagsField};
        
        return Strings::explode($tags);
    }
    
    public function tagLinks() : array
    {
        $tab = static::getTagsEntityType();
        $tags = $this->getTags();
        
        return array_map(
            function($t) use ($tab) {
                return new TagLink($t, $tab);
            },
            $tags
        );
    }

    public static function getByTag(string $tag, Query $query = null) : Query
    {
        $tag = Strings::normalize($tag);
        $ids = Tag::getIdsByTag(static::getTable(), $tag);

        if ($ids->empty()) {
            return Query::empty();
        }
        
        $query = $query ?? self::query();
        $query = $query->whereIn('id', $ids);

        if (method_exists(static::class, 'tagsWhere')) {
            $query = static::tagsWhere($query);
        }
        
        return $query;
    }
}
