<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Interfaces\PageInterface;

class PageCollection extends DbModelCollection
{
    protected string $class = PageInterface::class;
}
