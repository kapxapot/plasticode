<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Collections\Basic\TypedCollection;

class CollectionDummy extends TypedCollection
{
    protected string $class = ModelDummy::class;
}
