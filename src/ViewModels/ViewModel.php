<?php

namespace Plasticode\ViewModels;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Classes;

abstract class ViewModel implements \JsonSerializable, ArrayableInterface
{
    /**
     * Public method names that must be excluded from toArray() and serialization.
     * 
     * @var string[]
     */
    protected static $methodsToExclude = [];

    public function __construct()
    {
    }

    public function toArray() : array
    {
        $exclude = array_merge(
            static::$methodsToExclude,
            ['__construct', 'toArray', 'jsonSerialize']
        );

        $publicMethods = Classes::getPublicMethods(static::class, $exclude);

        $array = [];

        foreach ($publicMethods as $method) {
            $array[$method] = $this->{$method}();
        }

        return $array;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
