<?php

namespace Plasticode\Util;

use Webmozart\Assert\Assert;

class Arrays
{
    const IdField = 'id';
    const Dot = '.';

    /**
     * Checks if the key is present in the array.
     *
     * @param array $array
     * @param string|integer|null $key
     * @return boolean
     */
    public static function exists(array $array, $key) : bool
    {
        return !is_null($array[$key] ?? null);
    }
    
    /**
     * Get an item from an associative array using "dot" notation.
     * Taken from Illuminate/Support/Arr.
     *
     * @param array $array
     * @param string|integer|null $key
     * @return mixed
     */
    public static function get(array $array, $key)
    {
        if (empty($array) || strlen($key) == 0) {
            return null;
        }

        if (self::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode(self::Dot, $key) as $segment) {
            if (!self::exists($array, $segment)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * Taken from Illuminate/Support/Arr.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array|null
     */
    public static function set(array &$array, $key, $value) : ?array
    {
        Assert::notNull($key);

        $keys = explode(self::Dot, $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
    
    /**
     * Returns distinct values from array grouped by 'id'.
     * 
     * @param array $array
     * @return array
     */
    public static function distinctById(array $array) : array
    {
        return self::distinctBy($array, self::IdField);
    }
    
    /**
     * Returns distinct values from array grouped by selector.
     * 
     * @param array $array
     * @param string|\Closure $by Column/property name or Closure, returning generated column/property name.
     * @return array
     */
    public static function distinctBy(array $array, $by) : array
    {
        return array_values(self::toAssocBy($array, $by));
    }
    
    /**
     * Converts array to associative array by 'id'.
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     * 
     * @param array $array
     * @return array
     */
    public static function toAssocById(array $array) : array
    {
        return self::toAssocBy($array, self::IdField);
    }
    
    /**
     * Converts array to associative array by column/property or Closure.
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     * 
     * @param array $array
     * @param string|\Closure $by Column/property name or Closure, returning generated column/property name.
     * @return array
     */
    public static function toAssocBy(array $array, $by) : array
    {
        $groups = self::groupBy($array, $by);
        
        array_walk(
            $groups,
            function (&$item, $key) {
                $item = $item[0];
            }
        );
        
        return $groups;
    }
    
    /**
     * Groups array by 'id'.
     * 
     * @param array $array
     * @return array
     */
    public static function groupById(array $array) : array
    {
        return self::groupBy($array, self::IdField);
    }
    
    /**
     * Groups array by column/property or Closure.
     * 
     * @param array $array
     * @param string|\Closure $by Column/property name or Closure, returning generated column/property name.
     * @return array
     */
    public static function groupBy(array $array, $by) : array
    {
        if (empty($array)) {
            return [];
        }

        $result = [];

        foreach ($array as $element) {
            $key = $by instanceof \Closure
                ? $by($element)
                : self::getProperty($element, $by);
            
            $result[$key][] = $element;
        }
        
        return $result;
    }

    /**
     * Extracts non-null 'id' column/property values.
     * 
     * @param array $array
     * @return array
     */
    public static function extractIds(array $array) : array
    {
        return self::extract($array, self::IdField);
    }
    
    /**
     * Extracts unique (!) non-null column/property values from array.
     * 
     * @param array $array
     * @param string $column
     * @return array
     */
    public static function extract(array $array, string $column) : array
    {
        if (empty($array)) {
            return [];
        }
        
        $values = array_map(
            function ($item) use ($column) {
                return self::getProperty($item, $column);
            },
            $array
        );

        $values = array_filter(
            array_unique($values),
            function ($item) {
                return !is_null($item);
            }
        );

        return array_values($values);
    }
    
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
    
    /**
     * Filters array by column/property value or Closure,
     * then returns first item or null.
     * 
     * @param array $array
     * @param string|\Closure
     * @param mixed $value
     * @return mixed
     */
    public static function firstBy(array $array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::first($filtered);
    }
    
    /**
     * Filters array by column/property value or Closure,
     * then returns last item or null.
     * 
     * @param array $array
     * @param string|\Closure
     * @param mixed $value
     * @return mixed
     */
    public static function lastBy(array $array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::last($filtered);
    }
    
    /**
     * Filters array by column/property value or Closure.
     * 
     * @param array $array
     * @param string|\Closure $by
     * @param mixed $value
     * @return array
     */
    public static function filter(array $array, $by, $value = null) : array
    {
        Assert::true(
            $by instanceof \Closure && is_null($value)
            ||
            is_string($by) && !is_null($value),
            '$by must be a property/column with provided $value, or it must be a Closure without $value.'
        );

        $values = array_filter(
            $array,
            function ($item) use ($by, $value) {
                return $by instanceof \Closure
                    ? $by($item)
                    : self::getProperty($item, $by) == $value;
            }
        );

        return array_values($values);
    }
    
    /**
     * Filters array by specified column values.
     *
     * @param array $array
     * @param string $column
     * @param array $values
     * @return array
     */
    public static function filterIn(array $array, string $column, array $values) : array
    {
        return self::filter(
            $array,
            function ($item) use ($column, $values) {
                return in_array(self::getProperty($item, $column), $values);
            }
        );
    }
    
    /**
     * Filters array by all column values except specified.
     *
     * @param array $array
     * @param string $column
     * @param array $values
     * @return array
     */
    public static function filterNotIn(array $array, string $column, array $values) : array
    {
        return self::filter(
            $array,
            function ($item) use ($column, $values) {
                return !in_array(self::getProperty($item, $column), $values);
            }
        );
    }
    
    /**
     * Removes empty strings and nulls from array.
     */
    public static function clean(array $array) : array
    {
        return array_filter($array, function ($item) {
            return strlen($item) > 0;
        });
    }
    
    /**
     * Skips $offset elements from the start and returns the remaining array.
     */
    public static function skip(array $array, int $offset) : array
    {
        return array_slice($array, $offset);
    }
    
    /**
     * Returns first $limit elements.
     */
    public static function take(array $array, int $limit) : array
    {
        return array_slice($array, 0, $limit);
    }
    
    /**
     * Skips $offset elements and takes $limit elements.
     */
    public static function slice(array $array, int $offset, int $limit = null) : array
    {
        return array_slice($array, $offset, $limit);
    }
    
    /**
     * Returns first item from array or null.
     * 
     * @return mixed
     */
    public static function first($array)
    {
        return !empty($array) ? reset($array) : null;
    }
    
    /**
     * Returns last item from array or null.
     * 
     * @return mixed
     */
    public static function last($array)
    {
        return !empty($array) ? end($array) : null;
    }
    
    /**
     * Filters associative array by provided key set.
     * If key is absent, it is ignored.
     */
    public static function filterKeys($array, array $keys) : array
    {
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $result[$key] = $array[$key];
            }
        }
        
        return $result;
    }
    
    public static function orderBy($array, $column, $dir = null, $type = null) : array
    {
        return Sort::by($array, $column, $dir, $type);
    }
    
    public static function orderByDesc($array, $column, $type = null) : array
    {
        return Sort::desc($array, $column, $type);
    }
    
    public static function orderByStr($array, $column, $dir = null) : array
    {
        return Sort::byStr($array, $column, $dir);
    }
    
    public static function orderByStrDesc($array, $column) : array
    {
        return Sort::descStr($array, $column);
    }
    
    public static function multiSort($array, $sorts) : array
    {
        return Sort::multi($array, $sorts);
    }
}
