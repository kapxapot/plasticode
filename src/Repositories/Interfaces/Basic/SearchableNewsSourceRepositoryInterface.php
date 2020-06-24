<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\NewsSourceCollection;
use Plasticode\Models\Interfaces\NewsSourceInterface;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, ProtectedRepositoryInterface, SearchableRepositoryInterface
{
    function getProtected(?int $id) : ?NewsSourceInterface;
    function search(string $searchQuery) : NewsSourceCollection;
}
