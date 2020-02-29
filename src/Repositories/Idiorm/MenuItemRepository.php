<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuItemRepository extends IdiormRepository implements MenuItemRepositoryInterface
{
    public function get(int $id) : ?MenuItem
    {
        return MenuItem::get($id);
    }

    public function getByMenu(int $menuId) : Collection
    {
        return MenuItem::query()
            ->where(MenuItem::ParentIdField, $menuId)
            ->all();
    }
}
