<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;
use Plasticode\Models\MenuItem;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;

class MenuItemRepository extends IdiormRepository implements MenuItemRepositoryInterface
{
    protected string $entityClass = MenuItem::class;

    protected string $parentIdField = 'menu_id';

    private LinkerInterface $linker;

    public function __construct(
        Db $db,
        LinkerInterface $linker
    )
    {
        parent::__construct($db);

        $this->linker = $linker;
    }

    /**
     * @param MenuItem $entity
     */
    protected function hydrate(DbModel $entity) : MenuItem
    {
        return $entity
            ->withUrl(
                $this->linker->rel($entity->link)
            );
    }

    public function get(?int $id) : ?MenuItem
    {
        return $this->getEntity($id);
    }

    public function getByMenu(int $menuId) : Collection
    {
        return $this
            ->query()
            ->where($this->parentIdField, $menuId)
            ->all();
    }
}
