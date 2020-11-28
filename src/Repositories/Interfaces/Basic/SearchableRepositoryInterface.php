<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\Basic\DbModelCollection;

interface SearchableRepositoryInterface extends RepositoryInterface
{
    function search(string $searchQuery) : DbModelCollection;
}
