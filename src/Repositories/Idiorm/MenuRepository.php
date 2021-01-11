<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collections\MenuCollection;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;

class MenuRepository extends IdiormRepository implements MenuRepositoryInterface
{
    protected string $sortField = 'position';

    protected function entityClass(): string
    {
        return Menu::class;
    }

    public function get(?int $id): ?Menu
    {
        return $this->getEntity($id);
    }

    public function getAll(): MenuCollection
    {
        return MenuCollection::from(
            $this->query()
        );
    }
}
