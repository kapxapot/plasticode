<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Interfaces\PageInterface;

class PageCollectionDummy extends DbModelCollection
{
    protected string $class = PageInterface::class;
}
