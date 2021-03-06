<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\DbModelCollection;

interface SearchableRepositoryInterface extends RepositoryInterface
{
    public function search(string $query): DbModelCollection;
}
