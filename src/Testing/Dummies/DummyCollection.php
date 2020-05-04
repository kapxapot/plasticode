<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Collections\Basic\TypedCollection;

class DummyCollection extends TypedCollection
{
    protected string $class = DummyModel::class;
}
