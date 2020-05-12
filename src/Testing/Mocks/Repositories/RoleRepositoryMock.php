<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\RoleCollection;
use Plasticode\Models\Role;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RoleRepositoryMock implements RoleRepositoryInterface
{
    private RoleCollection $roles;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->roles = RoleCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Role
    {
        return $this->roles->first('id', $id);
    }
}
