<?php

namespace Plasticode\Collections\Generic;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\SortStep;
use Webmozart\Assert\Assert;

class Collection implements ArrayAccess, Iterator, Countable, JsonSerializable, ArrayableInterface
{
    protected array $data;

    protected function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    /**
     * Creates collection from element(s).
     *
     * @return static
     */
    public static function collect(...$items): self
    {
        return static::make($items);
    }

    /**
     * Creates collection from array.
     * 
     * @return static
     */
    public static function make(?array $data = null): self
    {
        return new static($data);
    }

    /**
     * Creates collection from arrayable (including other collection).
     * 
     * @return static
     */
    public static function from(ArrayableInterface $arrayable): self
    {
        return static::make(
            $arrayable->toArray()
        );
    }

    /**
     * Creates empty collection, shortcut to make().
     *
     * @return static
     */
    public static function empty(): self
    {
        return static::make();
    }

    /**
     * @return static
     */
    public function add(...$items): self
    {
        $col = static::make($items);

        return $this->concat($col);
    }

    /**
     * Removes first element by column/property value or callable.
     * 
     * @param string|callable|null $by
     * @param mixed $value
     * @return mixed
     */
    public function removeFirst($by = null, $value = null): self
    {
        $data = Arrays::removeFirstBy($this->data, $by, $value);

        return static::make($data);
    }

    /**
     * Concats the collection.
     * 
     * @param static $other
     * @return static
     */
    public function concat(self $other): self
    {
        $data = array_merge($this->data, $other->toArray());

        return static::make($data);
    }

    /**
     * Merges several collections.
     * 
     * @param static[] $collections
     * @return static
     */
    public static function merge(self ...$collections): self
    {
        $merged = static::empty();

        foreach ($collections as $collection) {
            $merged = $merged->concat($collection);
        }

        return $merged;
    }

    /**
     * Returns distinct values grouped by selector.
     * 
     * @param string|callable $by Column/property name or callable,
     * returning generated column/property name.
     * @return static
     */
    public function distinctBy($by): self
    {
        $data = Arrays::distinctBy($this->data, $by);

        return static::make($data);
    }

    /**
     * Converts collection to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken, others are discarded.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name.
     * @return array<string, mixed>
     */
    public function toAssoc($by = null): array
    {
        return Arrays::toAssocBy($this->data, $by);
    }

    /**
     * Groups collection by column/property or callable.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name.
     * @return array<string, static>
     */
    public function group($by = null): array
    {
        $result = [];

        $groups = Arrays::groupBy($this->data, $by);

        foreach ($groups as $key => $group) {
            $result[$key] = static::make($group);
        }

        return $result;
    }

    /**
     * Flattens a collection of elements, arrays and collections one level.
     * 
     * Does not make collection distinct!
     */
    public function flatten(): self
    {
        $data = [];

        foreach ($this->data as $item) {
            if (is_array($item) || $item instanceof self) {
                foreach ($item as $subItem) {
                    $data[] = $subItem;
                }
            } else {
                $data[] = $item;
            }
        }

        return self::make($data);
    }

    /**
     * Skips $offset elements from the start and returns the remaining collection.
     * 
     * @return static
     */
    public function skip(int $offset): self
    {
        $data = Arrays::skip($this->data, $offset);

        return static::make($data);
    }

    /**
     * Returns first $limit elements.
     * 
     * @return static
     */
    public function take(int $limit): self
    {
        $data = Arrays::take($this->data, $limit);

        return static::make($data);
    }

    /**
     * Skips $offset elements and takes $limit elements.
     * Negative $offset is counted from the end backwards.
     * 
     * @return static
     */
    public function slice(int $offset, int $limit): self
    {
        $data = Arrays::slice($this->data, $offset, $limit);

        return static::make($data);
    }

    /**
     * Returns last $limit elements.
     * 
     * @return static
     */
    public function tail(int $limit): self
    {
        $offset = $this->count() - $limit;

        if ($offset <= 0) {
            return $this;
        }

        return $this->skip($offset);
    }

    /**
     * Removes $limit elements from the end of collection (backward skip).
     * 
     * @return static
     */
    public function trimTail(int $limit): self
    {
        $data = Arrays::trimTail($this->data, $limit);

        return static::make($data);
    }

    /**
     * Returns random item or null if there are none.
     * 
     * @return mixed
     */
    public function random()
    {
        $count = $this->count();

        if ($count == 0) {
            return null;
        }

        $offset = mt_rand(0, $count - 1);

        return $this->slice($offset, 1)->first();
    }

    /**
     * Shuffles the collection's elements.
     *
     * @return static
     */
    public function shuffle(): self
    {
        $data = Arrays::shuffle($this->data);

        return static::make($data);
    }

    /**
     * Converts collection to ScalarCollection, optionally
     * mapping (reducing) its values to scalars.
     * 
     * In case of non-scalar values will throw an \InvalidArgumentException.
     * 
     * @param string|callable|null $by Column/property name or callable, that
     * produces scalar value.
     */
    public function scalarize($by = null): ScalarCollection
    {
        $col = $this;

        if (is_string($by)) {
            $col = $col->extract($by);
        } elseif (is_callable($by)) {
            $col = $col->map($by);
        }

        return ScalarCollection::from($col);
    }

    /**
     * Extracts non-null column/property values from collection.
     */
    public function extract(string $column): self
    {
        $data = Arrays::extract($this->data, $column);

        return self::make($data);
    }

    /**
     * Is there any value in this collection?
     * 
     * Uses 'where' filtering internally.
     * Suitable for looking for 'null' as well.
     *
     * @param string|callable|null $by
     * @param mixed $value
     */
    public function any($by = null, $value = null): bool
    {
        return $by
            ? $this->where($by, $value)->any()
            : !$this->isEmpty();
    }

    /**
     * Looks for any first non-null value in collection.
     * 
     * Uses 'first' internally, iterating the collection.
     * Not suitable for looking for 'null'.
     *
     * @param string|callable|null $by
     * @param mixed $value
     */
    public function anyFirst($by = null, $value = null): bool
    {
        return $this->first($by, $value) !== null;
    }

    public function isEmpty(): bool
    {
        return $this->count() == 0;
    }

    /**
     * Filters collection by column/property value or callable,
     * then returns first item or null.
     * 
     * @param string|callable|null $by
     * @param mixed $value
     * @return mixed
     */
    public function first($by = null, $value = null)
    {
        return $by
            ? Arrays::firstBy($this->data, $by, $value)
            : Arrays::first($this->data);
    }

    /**
     * Filters collection by column/property value or callable,
     * then returns last item or null.
     * 
     * @param string|callable|null $by
     * @param mixed $value
     * @return mixed
     */
    public function last($by = null, $value = null)
    {
        return $by
            ? Arrays::lastBy($this->data, $by, $value)
            : Arrays::last($this->data);
    }

    /**
     * Filters collection by property value or callable.
     * 
     * @param string|callable $by
     * @param mixed $value
     * @return static
     */
    public function where($by, $value = null): self
    {
        $data = Arrays::filter($this->data, $by, $value);

        return static::make($data);
    }

    /**
     * @param array|ArrayableInterface $values
     * @return static
     */
    public function whereIn(string $column, $values): self
    {
        $values = Arrays::adopt($values);
        $data = Arrays::filterIn($this->data, $column, $values);

        return static::make($data);
    }

    /**
     * @param array|ArrayableInterface $values
     * @return static
     */
    public function whereNotIn(string $column, $values): self
    {
        $values = Arrays::adopt($values);
        $data = Arrays::filterNotIn($this->data, $column, $values);

        return static::make($data);
    }

    /**
     * Shortcut for map()->flatten().
     */
    public function flatMap(callable $func): self
    {
        return $this
            ->map($func)
            ->flatten();
    }

    public function map(callable $func): self
    {
        $data = array_map($func, $this->data);

        return self::make($data);
    }

    /**
     * Applies callable to all collection's items.
     */
    public function apply(callable $func): void
    {
        array_walk($this->data, $func);
    }

    /**
     * Sorts collection ascending using property or callable.
     *
     * @param string|callable $by
     * @return static
     */
    public function asc($by, ?string $type = null): self
    {
        return $this->orderBy($by, null, $type);
    }

    /**
     * Sorts collection descending using property or callable.
     *
     * @param string|callable $by
     * @return static
     */
    public function desc($by, ?string $type = null): self
    {
        $data = Arrays::orderByDesc($this->data, $by, $type);

        return static::make($data);
    }

    /**
     * Sorts collection by column or callable.
     * Ascending by default.
     *
     * @param string|callable $by
     * @return static
     */
    public function orderBy($by, ?string $dir = null, ?string $type = null): self
    {
        $data = Arrays::orderBy($this->data, $by, $dir, $type);

        return static::make($data);
    }

    /**
     * Sorts collection ascending by column or callable as strings.
     *
     * @param string|callable $by
     * @return static
     */
    public function ascStr($by): self
    {
        return $this->orderByStr($by);
    }

    /**
     * Sorts collection descending by column or callable as strings.
     *
     * @param string|callable $by
     * @return static
     */
    public function descStr($by): self
    {
        $data = Arrays::orderByStrDesc($this->data, $by);

        return static::make($data);
    }

    /**
     * Sorts collection by column or callable as strings.
     * Ascending by default.
     *
     * @param string|callable $by
     * @return static
     */
    public function orderByStr($by, ?string $dir = null): self
    {
        $data = Arrays::orderByStr($this->data, $by, $dir);

        return static::make($data);
    }

    /**
     * Sorts collection using sort steps.
     *
     * @return static
     */
    public function sortBy(SortStep ...$steps): self
    {
        $data = Arrays::sortBy($this->data, ...$steps);

        return static::make($data);
    }

    /**
     * Sorts collection using comparer function
     * which receives two items to compare.
     *
     * @return static
     */
    public function orderByComparer(callable $comparer): self
    {
        $data = $this->data; // cloning
        usort($data, $comparer);

        return static::make($data);
    }

    /**
     * Reorders collection's items in reverse.
     *
     * @return static
     */
    public function reverse(): self
    {
        $data = array_reverse($this->data);

        return static::make($data);
    }

    /**
     * Removes nulls, empty strings and 0s.
     *
     * @return static
     */
    public function clean(): self
    {
        $data = Arrays::clean($this->data);

        return static::make($data);
    }

    /**
     * Returns implode() result on the underlying data with the provided delimiter.
     */
    public function join(string $delimiter = ''): string
    {
        return implode($delimiter, $this->data);
    }

    // ArrayAccess

    public function offsetSet($offset, $value)
    {
        Assert::notNull($offset);

        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset])
            ? $this->data[$offset]
            : null;
    }

    // Iterator

    public function rewind()
    {
        reset($this->data);
    }

    public function current()
    {
        return current($this->data);
    }

    public function key() 
    {
        return key($this->data);
    }

    public function next() 
    {
        return next($this->data);
    }

    public function valid()
    {
        $key = key($this->data);
        return ($key !== null && $key !== false);
    }

    // Countable

    public function count()
    {
        return count($this->data);
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    // ArrayableInterface

    public function toArray() : array
    {
        return $this->data;
    }

    // __toString()

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return get_class($this) . ' (' . $this->count() . ')';
    }
}
