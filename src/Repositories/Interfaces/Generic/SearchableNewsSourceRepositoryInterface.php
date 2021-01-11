<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\NewsSourceCollection;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, SearchableRepositoryInterface
{
    function search(string $searchQuery): NewsSourceCollection;
}
