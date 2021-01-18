<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Collections\Generic\TypedCollection;

class CollectionDummy extends TypedCollection
{
    protected string $class = ModelDummy::class;
}
