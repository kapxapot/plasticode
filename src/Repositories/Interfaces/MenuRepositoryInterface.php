<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\MenuCollection;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface MenuRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id): ?Menu;
    function getAll(): MenuCollection;
}
