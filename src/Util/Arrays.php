<?php

namespace Plasticode\Util;

use ArrayAccess;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\PropertyAccess;
use Webmozart\Assert\Assert;

class Arrays
{
    use PropertyAccess;

    private const DOT = '.';

    /**
     * Checks if `$array` is an array or implements {@see ArrayAccess}.
     *
     * @param array|ArrayAccess $array
     */
    private static function checkArray($array): void
    {
        Assert::true(
            is_array($array) || $array instanceof ArrayAccess,
            '$array must be an array or must implement ArrayAccess.'
        );
    }

    /**
     * Checks if the key is present in the array.
     *
     * @param array|ArrayAccess $array
     * @param string|integer|null $key
     */
    public static function exists($array, $key): bool
    {
        self::checkArray($array);

        return ($array[$key] ?? null) !== null;
    }

    /**
     * Get an item from an associative array using "dot" notation.
     *
     * Taken from {@see Illuminate\Support\Arr}.
     *
     * @param array|ArrayAccess $array
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
     *
     * Taken from {@see Illuminate\Support\Arr}.
     *
     * @param mixed $value
     */
    public static function set(array &$array, $key, $value): ?array
    {
        Assert::notNull($key);

        $keys = explode(self::DOT, $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

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
     * @param string|callable|null $by Column/property name or `callable`,
     * returning generated column/property name.
     */
    public static function distinctBy(array $array, $by): array
    {
        return array_values(
            self::toAssocBy($array, $by)
        );
    }

    /**
     * Converts array to associative array by column/property or `callable`.
     *
     * Selector must be unique, otherwise only first element is taken,
     * others are discarded.
     *
     * @param string|callable|null $by Column/property name or `callable`,
     * returning generated column/property name.
     * @return array<mixed, mixed>
     */
    public static function toAssocBy(array $array, $by): array
    {
        $groups = self::groupBy($array, $by);

        array_walk(
            $groups,
            fn (&$item, $key) => $item = $item[0]
        );

        return $groups;
    }

    /**
     * Groups array by column/property or `callable`.
     *
     * Note: If the filter is not `callable`, scalar values will go as
     * a key and value both, `null`s are ignored.
     *
     * @param string|callable|null $by Column/property name or `callable`, returning generated column/property name.
     * @return array<mixed, mixed>
     */
    public static function groupBy(array $array, $by): array
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
                    "Arrays::groupBy: $by can't be null for non-scalar."
                );

                $key = self::getProperty($element, $by);
            }

            $result[$key][] = $element;
        }

        return $result;
    }

    /**
     * Extracts unique (!) non-`null` column/property values from array.
     */
    public static function extract(array $array, string $column): array
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
            fn ($item) => $item !== null
        );

        return array_values($values);
    }

    /**
     * Filters the array by column/property value or `callable`,
     * then returns first item or `null`.
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
     * Filters the array by column/property value or `callable`,
     * then returns last item or `null`.
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
     * Warning: If the array contains any scalar values or `null`s, only callable can
     * be used.
     *
     * @param string|callable $by
     * @param mixed $value
     */
    public static function filter(array $array, $by, $value = null): array
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
    private static function satisfies($item, $by, $value = null): bool
    {
        $callable = isCallable($by);

        Assert::true(
            $callable && is_null($value)
            || is_string($by),
            '$by must be a property/column with provided $value, or it must be a callable without $value.'
        );

        Assert::true(
            $callable || !isScalar($item),
            '$by can be only callable if the $item is scalar or null.'
        );

        if ($callable) {
            return ($by)($item);
        }

        $propValue = self::getProperty($item, $by);

        return ($value === null && $propValue === null) || $propValue == $value;
    }

    /**
     * Filters the array by specified column values.
     */
    public static function filterIn(
        array $array,
        string $column,
        array $values
    ): array
    {
        return self::filter(
            $array,
            fn ($item) => in_array(
                self::getProperty($item, $column),
                $values
            )
        );
    }

    /**
     * Filters the array by all column values except specified.
     */
    public static function filterNotIn(
        array $array,
        string $column,
        array $values
    ): array
    {
        return self::filter(
            $array,
            fn ($item) => !in_array(
                self::getProperty($item, $column),
                $values
            )
        );
    }

    /**
     * Trims strings in the array and removes empty ones (cleans the array).
     *
     * @param string[] $strArray
     * @return string[]
     */
    public static function trim(array $strArray): array
    {
        $array = array_map(
            fn ($s) => trim($s),
            $strArray
        );

        return self::clean($array);
    }

    /**
     * Removes empty strings, `null`s and `0`s from the array.
     */
    public static function clean(array $array): array
    {
        return array_values(array_filter($array));
    }

    /**
     * Returns the first item from the array or `null`.
     *
     * @return mixed
     */
    public static function first(array $array)
    {
        return !empty($array) ? reset($array) : null;
    }

    /**
     * Returns the last item from the array or `null`.
     *
     * @return mixed
     */
    public static function last(array $array)
    {
        return !empty($array) ? end($array): null;
    }

    /**
     * Filters the associative array by provided key set.
     *
     * If a key is absent, it is ignored.
     */
    public static function filterKeys(array $array, array $keys): array
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
     * Skips `$offset` elements from the start and returns the remaining array.
     *
     * If the `$offset` is negative, starts backwards from the end towards it.
     */
    public static function skip(array $array, int $offset): array
    {
        return array_slice($array, $offset);
    }

    /**
     * Returns the first `$limit` elements.
     *
     * If the `$limit` is negative, takes all the elements from the start
     * until `|$limit|` from the end.
     */
    public static function take(array $array, int $limit): array
    {
        return array_slice($array, 0, $limit);
    }

    /**
     * Combines `skip()` and `take()`.
     */
    public static function slice(array $array, int $offset, int $limit): array
    {
        return array_slice($array, $offset, $limit);
    }

    /**
     * Removes `$limit` elements from the end of the array.
     */
    public static function trimTail(array $array, int $limit): array
    {
        return self::slice($array, 0, -$limit);
    }

    /**
     * Orders the array items, ascending / numeric by default.
     * A shortcut for `Sort::by()`.
     *
     * @param string|callable $by
     */
    public static function orderBy(
        array $array,
        $by,
        ?string $dir = null,
        ?string $type = null
    ): array
    {
        return Sort::by($array, $by, $dir, $type);
    }

    /**
     * Orders the array items descending, numeric by default.
     * A shortcut for Sort::desc().
     *
     * @param string|callable $by
     */
    public static function orderByDesc(array $array, $by, ?string $type = null): array
    {
        return Sort::desc($array, $by, $type);
    }

    /**
     * Orders the array items as strings, ascending by default.
     * A shortcut for Sort::byStr().
     *
     * @param string|callable $by
     */
    public static function orderByStr(array $array, $by, ?string $dir = null): array
    {
        return Sort::byStr($array, $by, $dir);
    }

    /**
     * Orders the array items descending as strings.
     * A shortcut for Sort::descStr().
     *
     * @param string|callable $by
     */
    public static function orderByStrDesc(array $array, $by): array
    {
        return Sort::descStr($array, $by);
    }

    /**
     * Orders the array items based on the array of sort conditions.
     * A shortcut for {@see Sort::multi()}.
     */
    public static function sortBy(array $array, SortStep ...$steps): array
    {
        return Sort::byMany($array, ...$steps);
    }

    /**
     * Adopts the array or {@see ArrayableInterface} and converts them to an array.
     *
     * @param array|ArrayableInterface|null $array
     */
    public static function adopt($array): ?array
    {
        if ($array === null) {
            return null;
        }

        if ($array instanceof ArrayableInterface) {
            $array = $array->toArray();
        }

        Assert::isArray(
            $array,
            'Error adopting an array: must be an ArrayableInterface or an array.'
        );

        return $array;
    }

    /**
     * Shuffles the array's elements.
     */
    public static function shuffle(array $array): array
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
    public static function removeFirstBy(array $array, $by, $value = null): array
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

    /**
     * Checks if the first array contains ALL elements from the second array.
     */
    public static function contains(array $first, array $second): bool
    {
        foreach ($second as $el) {
            if (!in_array($el, $first)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a copy of the array.
     */
    public static function clone(array $array): array
    {
        $new = $array;

        return $new;
    }

    /**
     * @param string|string[]|null $text
     * @return string[]|null
     */
    public static function arraify($text): ?array
    {
        if ($text === null) {
            return null;
        }

        return is_array($text)
            ? $text
            : [$text];
    }
}
