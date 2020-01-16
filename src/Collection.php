<?php

namespace Plasticode;

use Plasticode\Util\Arrays;

class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
    protected $data;
    
    protected function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public static function make(array $data = null) : self
    {
        return new static($data ?? []);
    }
    
    public static function makeEmpty() : self
    {
        return self::make();
    }
    
    public function add($item) : self
    {
        $col = self::make([ $item ]);
        return $this->concat($col);
    }
    
    public function concat(Collection $other) : self
    {
        $data = array_merge($this->data, $other->toArray());
        return self::make($data);
    }
    
    public static function merge(...$collections) : self
    {
        $merged = self::makeEmpty();
        
        foreach ($collections as $collection) {
            $merged = $merged->concat($collection);
        }

        return $merged;
    }

    /**
     * Returns distinct values grouped by selector ('id' by default).
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     */
    public function distinct($by = null) : self
    {
        $data = Arrays::distinctBy($this->data, $by ?? 'id');
        return self::make($data);
    }
    
    /**
     * Converts collection to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken, others are discarded.
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     */
    public function toAssoc($by = null) : array
    {
        return Arrays::toAssocBy($this->data, $by ?? 'id');
    }
    
    public function toArray() : array
    {
        return $this->data;
    }
    
    /**
     * Groups collection by column/property or callable.
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     * @return array Returns associative array of collections.
     */
    public function group($by = null) : array
    {
        $result = [];

        $groups = Arrays::groupBy($this->data, $by ?? 'id');
        
        foreach ($groups as $key => $group) {
            $result[$key] = self::make($group);
        }
        
        return $result;
    }
    
    /**
     * Flattens a collection of elements, arrays and collections one level.
     * 
     * Does not make collection distinct!
     */
    public function flatten() : self
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
     */
    public function skip(int $offset) : self
    {
        $data = Arrays::skip($this->data, $offset);
        return self::make($data);
    }
    
    /**
     * Returns first $limit elements.
     */
    public function take(int $limit) : self
    {
        $data = Arrays::take($this->data, $limit);
        return self::make($data);
    }
    
    /**
     * Skips $offset elements and takes $limit elements.
     * Negative $offset is counted from the end backwards.
     */
    public function slice(int $offset, int $limit = null) : self
    {
        $data = Arrays::slice($this->data, $offset, $limit);
        return self::make($data);
    }

    /**
     * Removes $limit elements from the end of collection (backward skip).
     */
    public function trimEnd(int $limit) : self
    {
        return $this->slice(-$limit);
    }

    /**
     * Removes 1 element from the end of collection.
     */
    public function pop() : self
    {
        return $this->trimEnd(1);
    }
    
    /**
     * Return random item.
     * 
     * @return mixed
     */
    public function random()
    {
        $count = $this->count();
        
        if ($count === 0) {
            return null;
        }
        
        $offset = rand(0, $count - 1);
        
        return $this->slice($offset, 1)->first();
    }

    /**
     * Extracts non-null 'id' column/property values.
     */
    public function ids() : self
    {
        return $this->extract('id');
    }

    /**
     * Extracts non-null column/property values from collection.
     */
    public function extract($column) : self
    {
        $data = Arrays::extract($this->data, $column);
        return self::make($data);
    }
    
    public function any($by = null, $value = null) : bool
    {
        if ($by !== null) {
            return $this
                ->where($by, $value)
                ->any();
        }

        return !$this->empty();
    }

    public function empty() : bool
    {
        return $this->count() == 0;
    }
    
    public function contains($value) : bool
    {
        return in_array($value, $this->data);
    }

    /**
     * Filters collection by column/property value or callable, then returns first item or null.
     * 
     * @return mixed
     */
    public function first($by = null, $value = null)
    {
        return $by
            ? Arrays::firstBy($this->data, $by, $value)
            : Arrays::first($this->data);
    }

    /**
     * Filters collection by column/property value or callable, then returns last item or null.
     * 
     * @return mixed
     */
    public function last($by = null, $value = null)
    {
        return $by
            ? Arrays::lastBy($this->data, $by, $value)
            : Arrays::last($this->data);
    }
    
    /**
     * Filters collection by column/property value or callable.
     */
    public function where($by, $value = null) : self
    {
        $data = Arrays::filter($this->data, $by, $value);
        return self::make($data);
    }
    
    public function whereIn($column, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        $data = Arrays::filterIn($this->data, $column, $values);
        return self::make($data);
    }
    
    public function whereNotIn($column, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        $data = Arrays::filterNotIn($this->data, $column, $values);
        return self::make($data);
    }

    public function map(\Closure $func) : self
    {
        $data = array_map($func, $this->data);
        return self::make($data);
    }
    
    public function apply(\Closure $func) : void
    {
        array_walk($this->data, $func);
    }
    
    public function asc($column, $type = null) : self
    {
        return $this->orderBy($column, null, $type);
    }
    
    public function desc($column, $type = null) : self
    {
        $data = Arrays::orderByDesc($this->data, $column, $type);
        return self::make($data);
    }

    public function orderBy(string $column, string $dir = null, string $type = null) : self
    {
        $data = Arrays::orderBy($this->data, $column, $dir, $type);
        return self::make($data);
    }
    
    public function ascStr($column) : self
    {
        return $this->orderByStr($column);
    }
    
    public function descStr($column) : self
    {
        $data = Arrays::orderByStrDesc($this->data, $column);
        return self::make($data);
    }

    public function orderByStr($column, $dir = null) : self
    {
        $data = Arrays::orderByStr($this->data, $column, $dir);
        return self::make($data);
    }
    
    public function multiSort($sorts) : self
    {
        $data = Arrays::multiSort($this->data, $sorts);
        return self::make($data);
    }

    public function orderByFunc(\Closure $func) : self
    {
        $data = $this->data; // cloning
        usort($data, $func);

        return self::make($data);
    }
    
    public function reverse() : self
    {
        $data = array_reverse($this->data);
        return self::make($data);
    }
    
    // ArrayAccess
    
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('$offset cannot be null.');
        } else {
            $this->data[$offset] = $value;
        }
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
}
