<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Role;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface RoleRepositoryInterface extends GetRepositoryInterface
{
    function get(?int $id): ?Role;
}
