<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\NewsSourceCollection;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, SearchableRepositoryInterface
{
    function search(string $searchQuery): NewsSourceCollection;
}
