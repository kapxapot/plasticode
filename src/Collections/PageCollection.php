<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\NewsSourceCollection;
use Plasticode\Models\Interfaces\PageInterface;

class PageCollection extends NewsSourceCollection
{
    protected string $class = PageInterface::class;
}
