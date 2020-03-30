<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\TypedCollection;

class DummyCollection extends TypedCollection
{
    protected string $class = DummyModel::class;
}
