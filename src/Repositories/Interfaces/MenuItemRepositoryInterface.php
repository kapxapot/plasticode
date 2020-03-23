<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\MenuItem;

interface MenuItemRepositoryInterface
{
    function get(?int $id) : ?MenuItem;
    function getByMenu(int $menuId) : Collection;
}
