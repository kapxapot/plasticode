<?php

namespace Plasticode\Util;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\PropertyAccess;
use Webmozart\Assert\Assert;

class Arrays
{
    use PropertyAccess;

    private const ID_FIELD = 'id';
    private const DOT = '.';

    /**
     * Checks if $array is an array or implements \ArrayAccess.
     *
     * @param array|\ArrayAccess $array
     */
    private static function checkArray($array) : void
    {
        Assert::true(
            is_array($array) || $array instanceof \ArrayAccess,
            '$array must be an array or implement \ArrayAccess.'
        );
    }

    /**
     * Checks if the key is present in the array.
     *
     * @param array|\ArrayAccess $array
     * @param string|integer|null $key
     */
    public static function exists($array, $key) : bool
    {
        self::checkArray($array);

        return !is_null($array[$key] ?? null);
    }

    /**
     * Get an item from an associative array using "dot" notation.
     * Taken from Illuminate\Support\Arr.
     *
     * @param array|\ArrayAccess $array
     * @param string|integer|null $key
     * @return mixed
     */
    public static function get($array, $key)
    {
        self::checkArray($array);
        
        if (empty($array) || strlen($key) == 0) {
            return null;
        }

        if (self::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode(self::DOT, $key) as $segment) {
            if (!self::exists($array, $segment)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * Taken from Illuminate\Support\Arr.
     *
     * @param mixed $value
     */
    public static function set(array &$array, $key, $value) : ?array
    {
        Assert::notNull($key);

        $keys = explode(self::DOT, $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create
            // an empty array to hold the next value, allowing us to create
            // the arrays to hold final values at the correct depth.
            // Then we'll keep digging into the array.
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
     */
    public static function distinctById(array $array) : array
    {
        return self::distinctBy($array, self::ID_FIELD);
    }

    /**
     * Returns distinct values from array grouped by selector.
     * 
     * @param string|callable $by Column/property name or callable, returning generated column/property name.
     */
    public static function distinctBy(array $array, $by) : array
    {
        return array_values(
            self::toAssocBy($array, $by)
        );
    }

    /**
     * Converts array to associative array by 'id'.
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     * 
     * @return array<string, mixed>
     */
    public static function toAssocById(array $array) : array
    {
        return self::toAssocBy($array, self::ID_FIELD);
    }

    /**
     * Converts array to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     * 
     * @param string|callable $by Column/property name or callable, returning generated column/property name.
     * @return array<string, mixed>
     */
    public static function toAssocBy(array $array, $by) : array
    {
        $groups = self::groupBy($array, $by);

        array_walk(
            $groups,
            fn (&$item, $key) => $item = $item[0]
        );

        return $groups;
    }

    /**
     * Groups array by 'id'.
     * 
     * @return array<string, mixed>
     */
    public static function groupById(array $array) : array
    {
        return self::groupBy($array, self::ID_FIELD);
    }

    /**
     * Groups array by column/property or callable.
     * 
     * @param string|callable $by Column/property name or callable, returning generated column/property name.
     * @return array<string, mixed>
     */
    public static function groupBy(array $array, $by) : array
    {
        if (empty($array)) {
            return [];
        }

        $result = [];

        foreach ($array as $element) {
            $key = is_callable($by)
                ? ($by)($element)
                : self::getProperty($element, $by);
            
            $result[$key][] = $element;
        }

        return $result;
    }

    /**
     * Extracts non-null 'id' column/property values.
     */
    public static function extractIds(array $array) : array
    {
        return self::extract($array, self::ID_FIELD);
    }

    /**
     * Extracts unique (!) non-null column/property values from array.
     */
    public static function extract(array $array, string $column) : array
    {
        if (empty($array)) {
            return [];
        }

        $values = array_map(
            fn ($item) => self::getProperty($item, $column),
            $array
        );

        $values = array_filter(
            array_unique($values),
            fn ($item) => !is_null($item)
        );

        return array_values($values);
    }

    /**
     * Filters array by column/property value or callable,
     * then returns first item or null.
     * 
     * @param string|callable
     * @param mixed $value
     * @return mixed
     */
    public static function firstBy(array $array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::first($filtered);
    }

    /**
     * Filters array by column/property value or callable,
     * then returns last item or null.
     * 
     * @param string|callable
     * @param mixed $value
     * @return mixed
     */
    public static function lastBy(array $array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::last($filtered);
    }

    /**
     * Filters array by column/property value or callable.
     * 
     * @param string|callable $by
     * @param mixed $value
     */
    public static function filter(array $array, $by, $value = null) : array
    {
        Assert::true(
            is_callable($by) && is_null($value)
            || is_string($by) && !is_null($value),
            '$by must be a property/column with provided $value, or it must be a callable without $value.'
        );

        $values = array_filter(
            $array,
            fn ($item) => 
            is_callable($by)
                ? ($by)($item)
                : self::getProperty($item, $by) == $value
        );

        return array_values($values);
    }

    /**
     * Filters array by specified column values.
     */
    public static function filterIn(
        array $array,
        string $column,
        array $values
    ) : array
    {
        return self::filter(
            $array,
            fn ($item) =>
            in_array(
                self::getProperty($item, $column),
                $values
            )
        );
    }

    /**
     * Filters array by all column values except specified.
     */
    public static function filterNotIn(
        array $array,
        string $column,
        array $values
    ) : array
    {
        return self::filter(
            $array,
            fn ($item) =>
            !in_array(
                self::getProperty($item, $column),
                $values
            )
        );
    }

    /**
     * Trims strings in array and removes empty ones (cleans array).
     *
     * @param string[] $strArray
     * @return string[]
     */
    public static function trim(array $strArray) : array
    {
        $array = array_map(
            fn ($s) => trim($s),
            $strArray
        );

        return self::clean($array);
    }

    /**
     * Removes empty strings, nulls and 0s from array.
     */
    public static function clean(array $array) : array
    {
        return array_values(array_filter($array));
    }

    /**
     * Returns first item from array or null.
     * 
     * @return mixed
     */
    public static function first(array $array)
    {
        return !empty($array) ? reset($array) : null;
    }

    /**
     * Returns last item from array or null.
     * 
     * @return mixed
     */
    public static function last(array $array)
    {
        return !empty($array) ? end($array) : null;
    }

    /**
     * Filters associative array by provided key set.
     * If key is absent, it is ignored.
     */
    public static function filterKeys(array $array, array $keys) : array
    {
        $result = [];

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $result[$key] = $array[$key];
            }
        }

        return $result;
    }

    /**
     * Skips $offset elements from the start and returns the remaining array.
     * If the $offset is negative, starts backwards from the end towards it.
     */
    public static function skip(array $array, int $offset) : array
    {
        return array_slice($array, $offset);
    }

    /**
     * Returns first $limit elements.
     * If the $limit is negative, takes all the elements from the start
     * until |$limit| from the end.
     */
    public static function take(array $array, int $limit) : array
    {
        return array_slice($array, 0, $limit);
    }

    /**
     * Combines skip() and take().
     */
    public static function slice(array $array, int $offset, int $limit) : array
    {
        return array_slice($array, $offset, $limit);
    }

    /**
     * Removes $limit (1 by default) elements from the array.
     */
    public static function trimEnd(array $array, int $limit = null) : array
    {
        return self::slice($array, 0, -($limit ?? 1));
    }

    /**
     * Orders array items, ascending / numeric by default.
     * Shortcut for Sort::by().
     * 
     * @param string|callable $by
     */
    public static function orderBy(
        array $array,
        $by,
        ?string $dir = null,
        ?string $type = null
    ) : array
    {
        return Sort::by($array, $by, $dir, $type);
    }

    /**
     * Orders array items descending, numeric by default.
     * Shortcut for Sort::desc().
     * 
     * @param string|callable $by
     */
    public static function orderByDesc(
        array $array,
        $by,
        ?string $type = null
    ) : array
    {
        return Sort::desc($array, $by, $type);
    }

    /**
     * Orders array items as strings, ascending by default.
     * Shortcut for Sort::byStr().
     * 
     * @param string|callable $by
     */
    public static function orderByStr(
        array $array,
        $by,
        ?string $dir = null
    ) : array
    {
        return Sort::byStr($array, $by, $dir);
    }

    /**
     * Orders array items descending as strings.
     * Shortcut for Sort::descStr().
     * 
     * @param string|callable $by
     */
    public static function orderByStrDesc(array $array, $by) : array
    {
        return Sort::descStr($array, $by);
    }

    /**
     * Orders array items based on array of sort conditions.
     * Shortcut for Sort::multi().
     *
     * @param SortStep[] $steps
     */
    public static function multiSort(array $array, array $steps) : array
    {
        return Sort::multi($array, $steps);
    }

    /**
     * Adopts array or ArrayableInterface and converts them to array.
     *
     * @param array|ArrayableInterface|null $array
     * @return array|null
     */
    public static function adopt($array) : ?array
    {
        if (is_null($array)) {
            return null;
        }

        if ($array instanceof ArrayableInterface) {
            $array = $array->toArray();
        }

        Assert::isArray(
            $array,
            'Error adopting array: it must be a ArrayableInterface or an array.'
        );

        return $array;
    }
}
