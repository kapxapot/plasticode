<?php

namespace Plasticode\Models\Traits;

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

    protected static function wherePublished($where = null)
    {
        return function ($q) use ($where) {
            $parentWhere = self::parentWherePublished(function ($q) use ($where) {
                $q = $q->whereRaw('(published_at < now())');
                
                if ($where) {
                    $q = $where($q);
                }
                
                return $q;
            });
            
            return $parentWhere($q);
	    };
    }
    
    // getters
    
    public static function getProtected($id, $where = null)
	{
		$editor = self::can('edit');
		
		$where = $where ?? function($q) use ($id) {
			return $q->where(static::$idField, $id);
		};

		return self::getBy(function ($q) use ($where, $editor) {
			$q = $where($q);

			if (!$editor) {
				$user = self::$auth->getUser();
				
				$published = "(published = 1 and published_at < now())";

				if ($user) {
					$q = $q->whereRaw("({$published} or created_by = ?)", [ $user->id ]);
				}
				else {
					$q = $q->whereRaw($published);
				}
			}
			
			return $q;
		});
	}

	// PROPS
	
	public function isPublished()
	{
	    return $this->parentIsPublished() && Date::happened($this->publishedAt);
	}
	
    public function publishedAtIso()
    {
        return $this->publishedAt ? Date::iso($this->publishedAt) : null;
    }
}
