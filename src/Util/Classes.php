<?php

namespace Plasticode\Util;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;

class Classes
{
    public static function shortName(string $className): string
    {
        return Strings::lastChunk($className, '\\');
    }

    /**
     * Returns public methods list of a class.
     *
     * @param string[]|null $exclude
     */
    public static function getPublicMethods(string $className, array $exclude = null): array
    {
        $class = new ReflectionClass($className);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $exclude = $exclude ?? [];
        $exclude[] = '__construct';

        $methodNames = array_map(
            fn (ReflectionMethod $method) => $method->name,
            $methods
        );

        $methodNames = array_filter(
            $methodNames,
            fn (string $methodName) => !in_array($methodName, $exclude)
        );

        return array_values($methodNames);
    }

    public static function getConstants(string $className): array
    {
        $class = new ReflectionClass($className);

        return $class->getConstants();
    }
}
