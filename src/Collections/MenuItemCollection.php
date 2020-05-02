<?php

namespace Plasticode\Collections;

use Plasticode\Models\MenuItem;
use Plasticode\TypedCollection;

class MenuItemCollection extends TypedCollection
{
    protected string $class = MenuItem::class;
}
