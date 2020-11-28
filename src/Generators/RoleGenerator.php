<?php

namespace Plasticode\Generators;

use Plasticode\Models\Role;

class RoleGenerator extends EntityGenerator
{
    protected function entityClass() : string
    {
        return Role::class;
    }
}
