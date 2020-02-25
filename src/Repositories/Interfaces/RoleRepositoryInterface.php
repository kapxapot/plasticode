<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Role;

interface RoleRepositoryInterface
{
    public function get(int $id) : ?Role;
}
