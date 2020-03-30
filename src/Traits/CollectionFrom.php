<?php

namespace Plasticode\Traits;

use Plasticode\Interfaces\ArrayableInterface;

trait CollectionFrom
{
    abstract public function make(?array $data = null) : self;

    /**
     * Creates collection from arrayable (including other Colection).
     */
    public static function from(ArrayableInterface $arrayable) : self
    {
        return self::make($arrayable->toArray());
    }
}
