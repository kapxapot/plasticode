<?php

namespace Plasticode\Util;

class Classes
{
    public static function shortName(string $class)
    {
        return Strings::lastChunk($class, '\\');
    }

    /**
     * Returns public methods list of a class.
     *
     * @param string[]|null $exclude
     */
    public static function getPublicMethods(string $class, array $exclude = null) : array
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $exclude = $exclude ?? [];
        $exclude[] = '__construct';

        $methodNames = array_map(
            function (\ReflectionMethod $method) {
                return $method->name;
            },
            $methods
        );

        $methodNames = array_filter(
            $methodNames,
            function (string $methodName) use ($exclude) {
                return !in_array($methodName, $exclude);
            }
        );

        return array_values($methodNames);
    }
}
