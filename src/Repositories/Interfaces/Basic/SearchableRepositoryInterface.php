<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\Basic\Collection;

interface SearchableRepositoryInterface
{
    function search(string $searchQuery) : Collection;
}
