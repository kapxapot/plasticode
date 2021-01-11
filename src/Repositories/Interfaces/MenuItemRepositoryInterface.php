<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface MenuItemRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id): ?MenuItem;
    function getAllByMenuId(int $menuId): MenuItemCollection;
}
