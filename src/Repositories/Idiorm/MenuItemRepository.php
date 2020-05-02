<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\Menu;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Util\SortStep;

class MenuItemRepository extends IdiormRepository implements MenuItemRepositoryInterface
{
    protected string $entityClass = MenuItem::class;

    protected string $parentIdField = 'menu_id';

    /**
     * @return SortStep[]
     */
    protected function getSortOrder() : array
    {
        return [
            SortStep::asc('position'),
            SortStep::asc('text')
        ];
    }

    public function get(?int $id) : ?MenuItem
    {
        return $this->getEntity($id);
    }

    public function getAllByMenuId(int $menuId) : MenuItemCollection
    {
        return MenuItemCollection::from(
            $this
                ->query()
                ->where($this->parentIdField, $menuId)
        );
    }
}
