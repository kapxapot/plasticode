<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Search\SearchParams;

interface SearchableRepositoryInterface extends RepositoryInterface
{
    public function search(SearchParams $searchParams): DbModelCollection;
}
