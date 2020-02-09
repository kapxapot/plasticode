<?php

namespace Plasticode\Util\Traits;

use Webmozart\Assert\Assert;

trait PropertyAccess
{
    /**
     * Returns property value.
     * 
     * @param mixed $obj
     * @param string $property
     * @return mixed
     */
    private static function getProperty($obj, string $property)
    {
        if (is_array($obj)) {
            return $obj[$property] ?? null;
        }

        Assert::object($obj);

        return isset($obj->{$property})
            ? $obj->{$property}
            : null;
    }
}
