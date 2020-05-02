<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuHydrator extends Hydrator
{
    protected MenuItemRepositoryInterface $menuItemRepository;
    protected LinkerInterface $linker;

    public function __construct(
        MenuItemRepositoryInterface $menuItemRepository,
        LinkerInterface $linker
    )
    {
        $this->menuItemRepository = $menuItemRepository;
        $this->linker = $linker;
    }

    /**
     * @param Menu $entity
     */
    public function hydrate(DbModel $entity) : Menu
    {
        return $entity
            ->withItems(
                fn () => $this->menuItemRepository->getAllByMenuId($entity->getId())
            )
            ->withUrl(
                fn () => $this->linker->rel($entity->link)
            );
    }
}
