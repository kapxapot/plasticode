<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;

class MenuRepository extends IdiormRepository implements MenuRepositoryInterface
{
    protected string $entityClass = Menu::class;

    protected ?string $sortField = 'position';

    private MenuItemRepositoryInterface $menuItemRepository;
    private LinkerInterface $linker;

    public function __construct(
        Db $db,
        MenuItemRepositoryInterface $menuItemRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct($db);

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

    public function get(?int $id) : ?Menu
    {
        return $this->getEntity($id);
    }
}
