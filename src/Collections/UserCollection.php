<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\User;

class UserCollection extends DbModelCollection
{
    protected string $class = User::class;
}
