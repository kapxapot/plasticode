<?php

namespace Plasticode\Util;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\PropertyAccess;
use Webmozart\Assert\Assert;

class Arrays
{
    use PropertyAccess;

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
     * Returns distinct values from array grouped by selector.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name.
     */
    public static function distinctBy(array $array, $by) : array
    {
        return array_values(
            self::toAssocBy($array, $by)
        );
    }

    /**
     * Converts array to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name.
     * @return array<mixed, mixed>
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
     * Groups array by column/property or callable.
     * 
     * Note:
     * If filter is not callable, scalar values will go as
     * key and value both, nulls are ignored.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name.
     * @return array<mixed, mixed>
     */
    public static function groupBy(array $array, $by) : array
    {
        if (empty($array)) {
            return [];
        }

        $result = [];

        foreach ($array as $element) {
            if (isCallable($by)) {
                $key = ($by)($element);
            } elseif (is_scalar($element)) {
                $key = $element;
            } elseif (is_null($element)) {
                continue;
            } else {
                Assert::notNull(
                    $by,
                    'Arrays::groupBy: $by can\'t be null for non-scalar.'
                );

                $key = self::getProperty($element, $by);
            }

            $result[$key][] = $element;
        }

        return $result;
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
        foreach ($array as $item) {
            if (self::satisfies($item, $by, $value)) {
                return $item;
            }
        }

        return null;
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
        for ($i = count($array) - 1; $i >= 0; $i--) {
            $item = $array[$i];

            if (self::satisfies($item, $by, $value)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Filters array by column/property value or callable.
     * 
     * Warning:
     * If the array contains any scalar values or nulls, only callable can
     * be used.
     * 
     * @param string|callable $by
     * @param mixed $value
     */
    public static function filter(array $array, $by, $value = null) : array
    {
        $values = array_filter(
            $array,
            fn ($i) => self::satisfies($i, $by, $value)
        );

        return array_values($values);
    }

    /**
     * Checks whether the item satisfies:
     * 
     * - Either ($item[$by] == $value) for property
     * - Or (($by)($item) == true) for callable.
     *
     * @param mixed $item
     * @param string|callable $by
     * @param mixed $value
     */
    private static function satisfies($item, $by, $value = null) : bool
    {
        $callable = isCallable($by);

        Assert::true(
            $callable && is_null($value)
            || is_string($by) && !is_null($value),
            '$by must be a property/column with provided $value, or it must be a callable without $value.'
        );

        Assert::true(
            $callable || !isScalar($item),
            '$by can be only callable if the $item is scalar or null.'
        );

        return $callable
            ? ($by)($item)
            : self::getProperty($item, $by) == $value;
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
     * Removes $limit elements from the end of array.
     */
    public static function trimTail(array $array, int $limit) : array
    {
        return self::slice($array, 0, -$limit);
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
     */
    public static function sortBy(array $array, SortStep ...$steps) : array
    {
        return Sort::byMany($array, ...$steps);
    }

    /**
     * Adopts array or ArrayableInterface and converts them to array.
     *
     * @param array|ArrayableInterface|null $array
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

    /**
     * Shuffles the array's elements.
     */
    public static function shuffle(array $array) : array
    {
        shuffle($array);
        return $array;
    }

    /**
     * Removes one element satisfying the criteria.
     * 
     * @param string|callable $by
     * @param mixed $value
     */
    public static function removeFirstBy(array $array, $by, $value = null) : array
    {
        if (empty($array)) {
            return $array;
        }

        $skipped = 0;

        foreach ($array as $item) {
            if (self::satisfies($item, $by, $value)) {
                break;
            }

            $skipped++;
        }

        if ($skipped == count($array)) {
            return $array;
        }

        return array_merge(
            self::take($array, $skipped),
            self::skip($array, $skipped + 1)
        );
    }
}
