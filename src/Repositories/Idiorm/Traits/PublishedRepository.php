<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;

/**
 * Limited publish support: only published (no published_at).
 */
trait PublishedRepository
{
    protected string $publishedField = 'published';

    protected function publishedQuery(Query $query = null) : Query
    {
        $query ??= $this->query();

        return $this->filterPublished($query);
    }

    abstract protected function query() : Query;

    protected function filterPublished(Query $query) : Query
    {
        return $query->where($this->publishedField, 1);
    }
}
