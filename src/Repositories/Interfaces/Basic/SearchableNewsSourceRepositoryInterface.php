<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\NewsSourceCollection;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, SearchableRepositoryInterface
{
    function search(string $searchQuery) : NewsSourceCollection;
}
