<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\MenuItem;

class MenuItemCollection extends DbModelCollection
{
    protected string $class = MenuItem::class;
}
