<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\Role;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends IdiormRepository implements RoleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    protected function entityClass() : string
    {
        return Role::class;
    }

    public function get(?int $id) : ?Role
    {
        return $this->getEntity($id);
    }
}
