<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collections\MenuCollection;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;

class MenuRepository extends IdiormRepository implements MenuRepositoryInterface
{
    protected string $entityClass = Menu::class;

    protected string $sortField = 'position';

    public function get(?int $id) : ?Menu
    {
        return $this->getEntity($id);
    }

    public function getAll() : MenuCollection
    {
        return MenuCollection::from(
            parent::getAll()
        );
    }
}
