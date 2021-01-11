<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Role;

class RoleCollection extends DbModelCollection
{
    protected string $class = Role::class;
}
