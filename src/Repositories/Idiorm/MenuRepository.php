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
    protected $entityClass = Menu::class;
    protected $sortField = 'position';

    /** @var MenuItemRepositoryInterface */
    private $menuItemRepository;

    /** @var LinkerInterface */
    private $linker;

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

    protected function ormObjToEntity(\ORM $ormObj) : DbModel
    {
        /** @var Menu */
        $menu = parent::ormObjToEntity($ormObj);

        return $menu
            ->withItems(
                $this->menuItemRepository->getByMenu($menu->id)
            )
            ->withUrl(
                $this->linker->rel($menu->link)
            );
    }

    public function get(int $id) : ?Menu
    {
        return $this->getEntity($id);
    }
}
