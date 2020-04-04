<?php

namespace Plasticode\Traits;

use Webmozart\Assert\Assert;

trait PropertyAccess
{
    /**
     * Returns property value.
     * 
     * @param mixed $obj
     * @return mixed
     */
    protected static function getProperty($obj, string $property)
    {
        if (is_array($obj)) {
            return $obj[$property] ?? null;
        }

        Assert::object($obj);

        return isset($obj->{$property})
            ? $obj->{$property}
            : null;
    }

    protected static function propertyExists($obj, string $property) : bool
    {
        if (is_array($obj)) {
            return array_key_exists($property, $obj);
        }

        Assert::object($obj);

        return property_exists($obj, $property);
    }
}
