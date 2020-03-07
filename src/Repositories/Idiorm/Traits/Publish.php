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
    protected function tagsWhereQuery(Query $query) : Query
    {
        return $this->wherePublishedQuery($query);
    }

    public function getPublishedQuery(Query $query) : Query
    {
        return $this->wherePublishedQuery($query);
    }

    protected function wherePublishedQuery(Query $query) : Query
    {
        return $query
            ->where(static::$publishedField, 1);
    }
}
