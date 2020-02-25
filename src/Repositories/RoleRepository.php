<?php

namespace Plasticode\Repositories;

use Plasticode\Models\Role;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends IdiormRepository implements RoleRepositoryInterface
{
    public function get(int $id) : ?Role
    {
        return Role::get($id);
    }
}
