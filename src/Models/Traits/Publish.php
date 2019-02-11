<?php

namespace Plasticode\Models\Traits;

/**
 * Limited publish support: only published (no published_at).
 */
trait Publish
{
    /**
     * For Tags trait.
     */
    protected static function tagsWhere($query)
    {
        $where = self::wherePublished();
        return $where($query);
    }
    
    protected static function wherePublished($where = null)
    {
        return function ($q) use ($where) {
            $q = $q->where('published', 1);
            
            if ($where) {
                $q = $where($q);
            }
            
            return $q;
	    };
    }
    
    // GETTERS - MANY
    
	public static function getAllPublished($where = null)
	{
	    return self::getAll(
	        self::wherePublished($where)
        );
	}
	
	// GETTERS - ONE
	
	public static function getPublished($id)
	{
	    return static::get($id, self::wherePublished());
	}
	
	public static function getPublishedWhere($where)
	{
	    return self::getBy(
	        self::wherePublished($where)
        );
	}
	
	// props
	
	public function isPublished()
	{
	    return $this->published == 1;
	}
}
