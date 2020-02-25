<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;

interface MenuItemRepositoryInterface
{
    public function getByMenu(int $menuId) : Collection;
}
