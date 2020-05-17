<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Page;

class PageCollection extends DbModelCollection
{
    protected string $class = Page::class;
}
