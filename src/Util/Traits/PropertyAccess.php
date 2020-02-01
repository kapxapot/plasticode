<?php

namespace Plasticode\Util\Traits;

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

        return property_exists($obj, $property)
            ? $obj->{$property}
            : null;
    }
}
