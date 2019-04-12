<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Util\Date;

/**
 * Limited publish support: only published (no published_at).
 */
trait Publish
{
    /**
     * For Tags trait.
     */
    protected static function tagsWhere(Query $query) : Query
    {
        return self::wherePublished($query);
    }

    // queries
    
	public static function getBasePublished() : Query
	{
	    return self::wherePublished(self::baseQuery());
	}
    
	public static function getPublished() : Query
	{
	    return self::wherePublished(self::query());
	}
    
    protected static function wherePublished(Query $query) : Query
    {
        return $query->where('published', 1);
    }
	
	// props & funcs

    public function publish()
    {
        if ($this->publishedAt === null) {
            $this->publishedAt = Date::dbNow();
        }
        
        $this->published = 1;
    }
	
	public function isPublished() : bool
	{
	    return $this->published == 1;
	}
}
