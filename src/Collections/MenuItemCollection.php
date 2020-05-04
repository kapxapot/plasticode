<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\MenuItem;

class MenuItemCollection extends DbModelCollection
{
    protected string $class = MenuItem::class;
}
