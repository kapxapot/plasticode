<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Menu;

class MenuCollection extends DbModelCollection
{
    protected string $class = Menu::class;
}
