<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;

interface SearchableRepositoryInterface
{
    function search(string $searchQuery) : Collection;
}
