<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\MenuItem;

interface MenuItemRepositoryInterface
{
    public function get(int $id) : ?MenuItem;
    public function getByMenu(int $menuId) : Collection;
}
