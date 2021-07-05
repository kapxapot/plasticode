<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;

interface FilteringRepositoryInterface extends RepositoryInterface
{
    public function getSearchResult(SearchParams $searchParams): SearchResult;
}
