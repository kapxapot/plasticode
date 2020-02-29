<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;

class MenuRepository extends IdiormRepository implements MenuRepositoryInterface
{
    public function get(int $id) : ?Menu
    {
        return Menu::get($id);
    }

    public function getAll() : Collection
    {
        return Menu::getAll();
    }
}
