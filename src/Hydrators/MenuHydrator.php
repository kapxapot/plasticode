<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Menu;
use Plasticode\Services\MenuService;

class MenuHydrator extends Hydrator
{
    protected MenuService $menuService;
    protected LinkerInterface $linker;

    public function __construct(
        MenuService $menuService,
        LinkerInterface $linker
    )
    {
        $this->menuService = $menuService;
        $this->linker = $linker;
    }

    /**
     * @param Menu $entity
     */
    public function hydrate(DbModel $entity): Menu
    {
        return $entity
            ->withItems(
                fn () => $this->menuService->getMenuItemsByMenu($entity)
            )
            ->withUrl(
                fn () => $this->linker->rel($entity->link)
            );
    }
}
