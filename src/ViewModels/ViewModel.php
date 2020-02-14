<?php

namespace Plasticode\ViewModels;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Classes;

abstract class ViewModel implements ArrayableInterface
{
    public function __construct()
    {
    }

    public function toArray() : array
    {
        $array = [];
        $publicMethods = Classes::getPublicMethods(static::class, ['__construct', 'toArray']);

        foreach ($publicMethods as $method) {
            $array[$method] = $this->{$method}();
        }

        return $array;
    }
}
