<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;

/**
 * Limited publish support: only published (no published_at).
 */
trait PublishedRepository
{
    protected string $publishedField = 'published';

    abstract protected function query() : Query;

    protected function publishedQuery() : Query
    {
        return $this->filterPublished(
            $this->query()
        );
    }

    protected function filterPublished(Query $query) : Query
    {
        return $query->where($this->publishedField, 1);
    }
}
