<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface MenuItemRepositoryInterface extends ChangingRepositoryInterface
{
    public function get(?int $id): ?MenuItem;

    public function getAll(): MenuItemCollection;

    public function getAllByMenuId(int $menuId): MenuItemCollection;
}
