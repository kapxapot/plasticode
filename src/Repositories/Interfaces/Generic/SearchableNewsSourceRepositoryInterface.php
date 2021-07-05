<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\NewsSourceCollection;
use Plasticode\Search\SearchParams;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, SearchableRepositoryInterface
{
    public function search(SearchParams $searchParams): NewsSourceCollection;
}
