<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;

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

    public static function getPublished() : Query
    {
        return self::wherePublished(self::query());
    }

    protected static function wherePublished(Query $query) : Query
    {
        return $query->where('published', 1);
    }

    public function publish()
    {
        $this->published = 1;
    }

    public function isPublished() : bool
    {
        return $this->published == 1;
    }
}
