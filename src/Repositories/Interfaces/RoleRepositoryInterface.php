<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Role;

interface RoleRepositoryInterface
{
    function get(?int $id) : ?Role;
}
