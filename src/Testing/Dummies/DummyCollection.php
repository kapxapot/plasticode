<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\TypedCollection;

class DummyCollection extends TypedCollection
{
    protected string $class = DummyModel::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }
}
