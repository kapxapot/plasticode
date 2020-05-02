<?php

namespace Plasticode\Collections;

use Plasticode\Models\Menu;
use Plasticode\TypedCollection;

class MenuCollection extends TypedCollection
{
    protected string $class = Menu::class;
}
