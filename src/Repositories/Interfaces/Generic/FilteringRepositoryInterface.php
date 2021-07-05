<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;

interface FilteringRepositoryInterface extends RepositoryInterface
{
    public function getFilteredResult(SearchParams $searchParams): SearchResult;

    public function getTotalCount(): int;

    public function getFilteredCount(SearchParams $searchParams): int;
}
