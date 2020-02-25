<?php

namespace Plasticode\Repositories;

use Plasticode\Collection;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuItemRepository extends IdiormRepository implements MenuItemRepositoryInterface
{
    public function getByMenu(int $menuId) : Collection
    {
        return MenuItem::query()
            ->where(MenuItem::ParentIdField, $menuId)
            ->all();
    }
}
