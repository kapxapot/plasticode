<?php

namespace Plasticode\Services;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\Menu;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuService
{
    private MenuItemRepositoryInterface $menuItemRepository;

    private ?MenuItemCollection $menuItems = null;

    public function __construct(
        MenuItemRepositoryInterface $menuItemRepository
    )
    {
        $this->menuItemRepository = $menuItemRepository;
    }

    public function getMenuItemsByMenu(Menu $menu): MenuItemCollection
    {
        return $this->getMenuItems()->where(
            fn (MenuItem $mi) => $mi->menuId == $menu->getId()
        );
    }

    public function getMenuItems(): MenuItemCollection
    {
        $this->menuItems ??= $this->menuItemRepository->getAll();

        return $this->menuItems;
    }
}
