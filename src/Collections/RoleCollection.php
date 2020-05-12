<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Role;

class RoleCollection extends DbModelCollection
{
    protected string $class = Role::class;
}
