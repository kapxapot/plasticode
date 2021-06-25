<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Collections\Generic\EquatableCollection;

class CollectionDummy extends EquatableCollection
{
    protected string $class = ModelDummy::class;
}
