<?php

namespace Plasticode\Traits;

use Webmozart\Assert\Assert;

trait PropertyAccess
{
    /**
     * Returns a property value.
     *
     * @param array|object $obj
     * @return mixed
     */
    protected static function getProperty($obj, string $property)
    {
        if (is_array($obj)) {
            return $obj[$property] ?? null;
        }

        Assert::object($obj);

        return $obj->{$property} ?? null;
    }

    /**
     * Checks if the array or the object has the property.
     *
     * @param array|object $obj
     */
    protected static function propertyExists($obj, string $property): bool
    {
        if (is_array($obj)) {
            return array_key_exists($property, $obj);
        }

        Assert::object($obj);

        return property_exists($obj, $property);
    }
}
