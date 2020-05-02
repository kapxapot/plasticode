<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\MenuItem;

interface MenuItemRepositoryInterface
{
    function get(?int $id) : ?MenuItem;
    function getAllByMenuId(int $menuId) : MenuItemCollection;
}
