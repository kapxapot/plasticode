<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuHydrator implements HydratorInterface
{
    private MenuItemRepositoryInterface $menuItemRepository;
    private LinkerInterface $linker;

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
    protected function hydrate(DbModel $entity) : Menu
    {
        return $entity
            ->withItems(
                $this->menuItemRepository->getByMenu($entity->getId())
            )
            ->withUrl(
                $this->linker->rel($entity->link)
            );
    }
}
