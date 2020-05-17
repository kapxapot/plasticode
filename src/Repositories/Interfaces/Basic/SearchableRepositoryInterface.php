<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\Basic\DbModelCollection;

interface SearchableRepositoryInterface
{
    function search(string $searchQuery) : DbModelCollection;
}
