<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;

/**
 * Limited publish support: only published (no published_at).
 */
trait Publish
{
    protected static $publishedField = 'published';

    /**
     * For Tags trait.
     */
    protected function tagsWhere(Query $query) : Query
    {
        return $this->wherePublished($query);
    }

    public function getPublished(Query $query) : Query
    {
        return $this->wherePublished($query);
    }

    protected function wherePublished(Query $query) : Query
    {
        return $query->where(static::$publishedField, 1);
    }
}
