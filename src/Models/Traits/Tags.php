<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\Tag;
use Plasticode\Models\TagLink;
use Plasticode\Util\Strings;

trait Tags
{
    protected static function getTagsEntityType()
    {
        return static::getTable();
    }
    
    /**
     * Returns tags as an array of TRIMMED strings
     */
    protected function getTags()
    {
        $tags = $this->{static::$tagsField};
        
		if (strlen($tags) > 0) {
			$result = array_map(function($t) {
				return trim($t);
			}, explode(',', $tags));
		}
		
		return $result ?? [];
    }
    
	public function tagLinks()
	{
	    $tab = static::getTagsEntityType();
	    $tags = $this->getTags();
	    
		return array_map(function($t) use ($tab) {
			return new TagLink($t, $tab);
		}, $tags);
	}

	public static function getByTag($tag, $where = null)
	{
		$tag = Strings::normalize($tag);
		$ids = Tag::getIdsByTag(static::getTable(), $tag);
		
		if ($ids->empty()) {
			return $ids;
		}
		
		return self::getAll(function ($q) use ($ids, $where) {
		    if (method_exists(static::class, 'tagsWhere')) {
		        $q = static::tagsWhere($q);
		    }
		    
    		$q = $q->whereIn('id', $ids->toArray());
       		
       		if ($where) {
       			$q = $where($q);
       		}
            
            return $q;
		});
	}
}
