<?php

namespace Plasticode;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;
use Webmozart\Assert\Assert;

class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable, ArrayableInterface
{
    /**
     * Empty collection
     */
    private static ?Collection $empty = null;

    protected array $data;

    protected function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    /**
     * Creates collection from array.
     * 
     * @return static
     */
    public static function make(?array $data = null) : self
    {
        return new static($data);
    }

    /**
     * Creates collection from arrayable (including other Colection).
     * 
     * @return static
     */
    public static function from(ArrayableInterface $arrayable) : self
    {
        return static::make(
            $arrayable->toArray()
        );
    }

    public static function empty() : Collection
    {
        if (is_null(self::$empty)) {
            self::$empty = Collection::make();
        }

        return self::$empty;
    }

    /**
     * @return static
     */
    public function add($item) : self
    {
        $col = static::make([$item]);
        return $this->concat($col);
    }

    /**
     * Concats the collection of the same type.
     * 
     * @param static $other
     * @return static
     */
    public function concat(self $other) : self
    {
        $data = array_merge($this->data, $other->toArray());
        return static::make($data);
    }

    /**
     * Merges several heterogenous collections.
     */
    public static function merge(Collection ...$collections) : Collection
    {
        $merged = Collection::empty();

        foreach ($collections as $collection) {
            $merged = $merged->concat($collection);
        }

        return $merged;
    }

    /**
     * Returns distinct values grouped by selector ('id' by default).
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     * @return static
     */
    public function distinct($by = null) : self
    {
        $data = Arrays::distinctBy($this->data, $by ?? 'id');
        return static::make($data);
    }

    /**
     * Converts collection to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken, others are discarded.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     * @return array<string, mixed>
     */
    public function toAssoc($by = null) : array
    {
        return Arrays::toAssocBy($this->data, $by ?? 'id');
    }

    /**
     * Groups collection by column/property or callable.
     * 
     * @param string|callable|null $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     * @return array<string, self>
     */
    public function group($by = null) : array
    {
        $result = [];

        $groups = Arrays::groupBy($this->data, $by ?? 'id');

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
    public function flatten() : Collection
    {
        $data = [];

        foreach ($this->data as $item) {
            if (is_array($item) || $item instanceof Collection) {
                foreach ($item as $subItem) {
                    $data[] = $subItem;
                }
            } else {
                $data[] = $item;
            }
        }

        return Collection::make($data);
    }

    /**
     * Skips $offset elements from the start and returns the remaining collection.
     * 
     * @return static
     */
    public function skip(int $offset) : self
    {
        $data = Arrays::skip($this->data, $offset);
        return static::make($data);
    }

    /**
     * Returns first $limit elements.
     * 
     * @return static
     */
    public function take(int $limit) : self
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
    public function slice(int $offset, int $limit = null) : self
    {
        $data = Arrays::slice($this->data, $offset, $limit);
        return static::make($data);
    }

    /**
     * Removes $limit elements from the end of collection (backward skip).
     * 
     * @return static
     */
    public function trimEnd(int $limit = null) : self
    {
        $data = Arrays::trimEnd($this->data, $limit);
        return static::make($data);
    }

    /**
     * Return random item.
     * 
     * @return mixed
     */
    public function random()
    {
        $count = $this->count();

        if ($count == 0) {
            return null;
        }

        $offset = rand(0, $count - 1);

        return $this->slice($offset, 1)->first();
    }

    /**
     * Extracts non-null 'id' column/property values.
     */
    public function ids() : Collection
    {
        $data = Arrays::extractIds($this->data);
        return Collection::make($data);
    }

    /**
     * Extracts non-null column/property values from collection.
     */
    public function extract($column) : Collection
    {
        $data = Arrays::extract($this->data, $column);
        return Collection::make($data);
    }

    /**
     * Is there any value in this collection?
     *
     * @param string|callable|null $by
     * @param mixed $value
     */
    public function any($by = null, $value = null) : bool
    {
        if ($by !== null) {
            return $this
                ->where($by, $value)
                ->any();
        }

        return !$this->isEmpty();
    }

    public function isEmpty() : bool
    {
        return $this->count() == 0;
    }

    public function contains($value) : bool
    {
        return in_array($value, $this->data);
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
    public function where($by, $value = null) : self
    {
        $data = Arrays::filter($this->data, $by, $value);
        return static::make($data);
    }

    /**
     * @param array|ArrayableInterface $values
     * @return static
     */
    public function whereIn(string $column, $values) : self
    {
        $values = Arrays::adopt($values);
        $data = Arrays::filterIn($this->data, $column, $values);

        return static::make($data);
    }

    /**
     * @param array|ArrayableInterface $values
     * @return static
     */
    public function whereNotIn(string $column, $values) : self
    {
        $values = Arrays::adopt($values);
        $data = Arrays::filterNotIn($this->data, $column, $values);

        return static::make($data);
    }

    public function map(callable $func) : Collection
    {
        $data = array_map($func, $this->data);
        return Collection::make($data);
    }

    /**
     * Applies callable to all collection's items.
     */
    public function apply(callable $func) : void
    {
        array_walk($this->data, $func);
    }

    /**
     * Sorts collection ascending using property or callable.
     *
     * @param string|callable $by
     * @return static
     */
    public function asc($by, ?string $type = null) : self
    {
        return $this->orderBy($by, null, $type);
    }

    /**
     * Sorts collection descending using property or callable.
     *
     * @param string|callable $by
     * @return static
     */
    public function desc($by, ?string $type = null) : self
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
    public function orderBy($by, ?string $dir = null, ?string $type = null) : self
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
    public function ascStr($by) : self
    {
        return $this->orderByStr($by);
    }

    /**
     * Sorts collection descending by column or callable as strings.
     *
     * @param string|callable $by
     * @return static
     */
    public function descStr($by) : self
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
    public function orderByStr($by, ?string $dir = null) : self
    {
        $data = Arrays::orderByStr($this->data, $by, $dir);
        return static::make($data);
    }

    /**
     * Sorts collection using sort steps.
     *
     * @param SortStep[] $steps
     * @return static
     */
    public function multiSort(array $steps) : self
    {
        $data = Arrays::multiSort($this->data, $steps);
        return static::make($data);
    }

    /**
     * Sorts collection using comparer function
     * which receives two items to compare.
     *
     * @return static
     */
    public function orderByComparer(callable $comparer) : self
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
    public function reverse() : self
    {
        $data = array_reverse($this->data);
        return static::make($data);
    }

    /**
     * Removes nulls, empty strings and 0s.
     *
     * @return static
     */
    public function clean() : self
    {
        $data = Arrays::clean($this->data);
        return static::make($data);
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
}
