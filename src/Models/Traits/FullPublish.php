<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Util\Date;

/**
 * Full publish support: published + published_at.
 */
trait FullPublish
{
    use Publish
    {
        Publish::wherePublished as protected parentWherePublished;
        isPublished as protected parentIsPublished;
    }

    // queries
    
    public static function getBaseProtected() : Query
    {
        return self::getProtected(self::baseQuery());
    }
    
    public static function getProtected(Query $query = null) : Query
	{
	    $query = $query ?? self::query();
	    
		$editor = self::can('edit');
		
		if ($editor) {
		    return $query;
		}

		$published = "(published = 1 and published_at < now())";

		$user = self::$auth->getUser();

		if ($user) {
			return $query->whereRaw("({$published} or created_by = ?)", [ $user->id ]);
		}
		
		return $query->whereRaw($published);
	}

	// props

    protected static function wherePublished(Query $query) : Query
    {
        return self::parentWherePublished($query)
            ->whereRaw('(published_at < now())');
    }
	
	public function isPublished() : bool
	{
	    return $this->parentIsPublished() && Date::happened($this->publishedAt);
	}
	
    public function publishedAtIso() : string
    {
        return $this->publishedAt ? Date::iso($this->publishedAt) : null;
    }
}
