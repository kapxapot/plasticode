<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\DbModelCollection;

interface SearchableRepositoryInterface extends RepositoryInterface
{
    function search(string $searchQuery): DbModelCollection;
}
