<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\NewsSourceCollection;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, SearchableRepositoryInterface
{
    public function search(string $query): NewsSourceCollection;
}
