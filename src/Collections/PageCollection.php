<?php

namespace Plasticode\Collections;

use Plasticode\Models\Interfaces\PageInterface;

class PageCollection extends NewsSourceCollection
{
    protected string $class = PageInterface::class;
}
