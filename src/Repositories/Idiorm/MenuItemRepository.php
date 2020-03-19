<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuItemRepository extends IdiormRepository implements MenuItemRepositoryInterface
{
    protected $entityClass = MenuItem::class;

    protected const ParentIdField = 'menu_id';

    protected function ormObjToEntity(\ORM $ormObj) : DbModel
    {
        /** @var MenuItem */
        $menuItem = parent::ormObjToEntity($ormObj);

        return $menuItem
            ->withUrl(
                $this->linker->rel($menuItem->link)
            );
    }

    public function get(int $id) : ?MenuItem
    {
        return $this->getEntity($id);
    }

    public function getByMenu(int $menuId) : Collection
    {
        return $this
            ->query()
            ->where(self::ParentIdField, $menuId)
            ->all();
    }
}
