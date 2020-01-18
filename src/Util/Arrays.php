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
     * Converts array to associative array by column/property or callable.
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
            $key = is_callable($by)
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
        
        return array_filter(
            array_unique($values),
            function ($item) {
                return !is_null($item);
            }
        );
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
     * Filters array by column/property value or callable, then returns first item or null.
     * 
     * @return mixed
     */
    public static function firstBy($array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::first($filtered);
    }
    
    /**
     * Filters array by column/property value or callable, then returns last item or null.
     * 
     * @return mixed
     */
    public static function lastBy($array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::last($filtered);
    }
    
    /**
     * Filters array by column/property value or callable.
     */
    public static function filter($array, $by, $value = null) : array
    {
        if ($array === null) {
            return null;
        }
        
        return array_filter($array, function ($item) use ($by, $value) {
            return is_callable($by)
                ? $by($item)
                : self::getProperty($item, $by) == $value;
        });
    }
    
    public static function filterIn($array, $column, $values) : array
    {
        return self::filter($array, function ($item) use ($column, $values) {
            return in_array(self::getProperty($item, $column), $values);
        });
    }
    
    public static function filterNotIn($array, $column, $values) : array
    {
        return self::filter($array, function ($item) use ($column, $values) {
            return !in_array(self::getProperty($item, $column), $values);
        });
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
