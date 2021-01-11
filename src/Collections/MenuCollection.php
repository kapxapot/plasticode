<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Menu;

class MenuCollection extends DbModelCollection
{
    protected string $class = Menu::class;
}
