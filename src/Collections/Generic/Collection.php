<?php

namespace Plasticode\Collections\Generic;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\SortStep;
use Webmozart\Assert\Assert;

class Collection implements ArrayableInterface, ArrayAccess, Countable, Iterator, JsonSerializable
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
     * Removes first element by selector.
     * 
     * @param string|callable|null $selector
     * @param mixed $value
     * @return static
     */
    public function removeFirst($selector = null, $value = null): self
    {
        $data = Arrays::removeFirstBy($this->data, $selector, $value);

        return static::make($data);
    }

    /**
     * Concats the other collection of the same type.
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
     * Merges several collections of the same type.
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
     * @param string|callable $selector Column/property name or callable,
     * returning generated column/property name.
     * @return static
     */
    public function distinctBy($selector): self
    {
        $data = Arrays::distinctBy($this->data, $selector);

        return static::make($data);
    }

    /**
     * Converts collection to associative array by selector.
     * 
     * Selector should produce unique results, otherwise only first element is taken,
     * others are discarded.
     * 
     * @param string|callable|null $selector Column/property name or callable, returning generated column/property name.
     * @return array<string, mixed>
     */
    public function toAssoc($selector = null): array
    {
        return Arrays::toAssocBy($this->data, $selector);
    }

    /**
     * Groups collection by selector.
     *
     * @param string|callable|null $selector Column/property name or callable, returning generated string or numeric value.
     * @return array<string, static>
     */
    public function group($selector = null): array
    {
        $result = [];

        $groups = Arrays::groupBy($this->data, $selector);

        foreach ($groups as $key => $group) {
            $result[$key] = static::make($group);
        }

        return $result;
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

        if ($count === 0) {
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
     * Converts collection to {@see StringCollection}, using $item->__toString() by default.
     * Can be customized by selector.
     *
     * Don't confuse with stringify!
     *
     * In case of non-string values will throw an {@see InvalidArgumentException}.
     *
     * @param string|callable|null $selector Column/property name or callable, that
     * produces a string value.
     * @throws InvalidArgumentException
     */
    public function stringize($selector = null): StringCollection
    {
        return StringCollection::from(
            $this->scalarize(
                $selector ?? (fn ($item) => (string)$item)
            )
        );
    }

    /**
     * Converts collection to {@see NumericCollection}, optionally
     * mapping (reducing) its values to numeric values.
     *
     * In case of non-numeric values will throw an {@see InvalidArgumentException}.
     *
     * @param string|callable|null $selector Column/property name or callable, that
     * produces a numeric value.
     * @throws InvalidArgumentException
     */
    public function numerize($selector = null): NumericCollection
    {
        return NumericCollection::from(
            $this->scalarize($selector)
        );
    }

    /**
     * Converts collection to {@see ScalarCollection}, optionally
     * mapping (reducing) its values to scalars.
     *
     * In case of non-scalar values will throw an {@see InvalidArgumentException}.
     *
     * @param string|callable|null $selector Column/property name or callable, that
     * produces a scalar value.
     * @throws InvalidArgumentException
     */
    public function scalarize($selector = null): ScalarCollection
    {
        $col = $this;

        if (is_string($selector)) {
            $col = $col->extract($selector);
        } elseif (is_callable($selector)) {
            $col = $col->map($selector);
        }

        return ScalarCollection::from($col);
    }

    /**
     * Alias for `extract()`.
     */
    public function pluck(string $column): Collection
    {
        return $this->extract($column);
    }

    /**
     * Extracts non-null column/property values from collection.
     */
    public function extract(string $column): Collection
    {
        $data = Arrays::extract($this->data, $column);

        return Collection::make($data);
    }

    /**
     * Is there any value in this collection?
     *
     * Uses `where` filtering internally.
     * Suitable for looking for `null` as well.
     *
     * @param string|callable|null $selector
     * @param mixed $value
     */
    public function any($selector = null, $value = null): bool
    {
        return $selector
            ? $this->where($selector, $value)->any()
            : !$this->isEmpty();
    }

    /**
     * Is there any first non-null value in this collection?
     *
     * Uses `first()` internally, iterating the collection.
     * Not suitable for looking for `null`.
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
        return $this->count() === 0;
    }

    /**
     * Filters collection by selector, then returns first item or null.
     * 
     * @param string|callable|null $selector
     * @param mixed $value
     * @return mixed
     */
    public function first($selector = null, $value = null)
    {
        return $selector
            ? Arrays::firstBy($this->data, $selector, $value)
            : Arrays::first($this->data);
    }

    /**
     * Filters collection by selector, then returns last item or null.
     * 
     * @param string|callable|null $selector
     * @param mixed $value
     * @return mixed
     */
    public function last($selector = null, $value = null)
    {
        return $selector
            ? Arrays::lastBy($this->data, $selector, $value)
            : Arrays::last($this->data);
    }

    /**
     * Filters collection by selector.
     *
     * In case of `$selector === null` returns the collection itself, not a copy.
     *
     * @param string|callable|null $selector
     * @param mixed $value
     * @return static|$this
     */
    public function where($selector, $value = null): self
    {
        if ($selector === null) {
            return $this;
        }

        $data = Arrays::filter($this->data, $selector, $value);

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
    public function flatMap(callable $func): Collection
    {
        return $this
            ->map($func)
            ->flatten();
    }

    /**
     * Shortcut for map()->clean().
     */
    public function cleanMap(callable $func): Collection
    {
        return $this
            ->map($func)
            ->clean();
    }

    public function map(callable $func): Collection
    {
        $data = array_map($func, $this->data);

        return Collection::make($data);
    }

    /**
     * Flattens a collection of elements, arrays and collections one level.
     * 
     * Doesn't make collection distinct!
     */
    public function flatten(): Collection
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
     * Applies callable to all collection's items.
     */
    public function apply(callable $func): void
    {
        array_walk($this->data, $func);
    }

    /**
     * Sorts collection ascending using selector.
     *
     * @param string|callable $selector
     * @return static
     */
    public function asc($selector, ?string $type = null): self
    {
        return $this->orderBy($selector, null, $type);
    }

    /**
     * Sorts collection descending using selector.
     *
     * @param string|callable $selector
     * @return static
     */
    public function desc($selector, ?string $type = null): self
    {
        $data = Arrays::orderByDesc($this->data, $selector, $type);

        return static::make($data);
    }

    /**
     * Sorts collection by selector.
     *
     * - Ascending by default.
     * - `Sort::NUMBER` type by default.
     *
     * @param string|callable $selector
     * @return static
     */
    public function orderBy($selector, ?string $dir = null, ?string $type = null): self
    {
        $data = Arrays::orderBy($this->data, $selector, $dir, $type);

        return static::make($data);
    }

    /**
     * Sorts collection as strings ascending by selector.
     *
     * @param string|callable $selector
     * @return static
     */
    public function ascStr($selector): self
    {
        return $this->orderByStr($selector);
    }

    /**
     * Sorts collection as strings descending by selector.
     *
     * @param string|callable $selector
     * @return static
     */
    public function descStr($selector): self
    {
        $data = Arrays::orderByStrDesc($this->data, $selector);

        return static::make($data);
    }

    /**
     * Sorts collection as strings by selector. Ascending by default.
     *
     * @param string|callable $selector
     * @return static
     */
    public function orderByStr($selector, ?string $dir = null): self
    {
        $data = Arrays::orderByStr($this->data, $selector, $dir);

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
     * Returns implode() result on the underlying data with the provided delimiter
     * (no delimiter by default).
     */
    public function join(?string $delimiter = null): string
    {
        return implode($delimiter ?? '', $this->data);
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

    public function toArray(): array
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
