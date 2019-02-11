<?php

namespace Plasticode;

use Plasticode\Util\Arrays;

class Collection implements \ArrayAccess, \Iterator, \Countable
{
    protected $data;
    
    protected function __construct($data)
    {
        $this->data = $data;
    }
    
    public static function make($data)
    {
        return new Collection($data);
    }
    
    /**
     * Returns distinct values grouped by selector ('id' by default).
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     */
    public function distinct($by = 'id')
    {
        $data = Arrays::distinctBy($this->data, $by);
        return self::make($data);
    }
    
    /**
     * Converts collection to associative array by column/property or callable.
     * Selector must be unique, otherwise only first element is taken, others are discarded.
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     */
    public function toAssoc($by = 'id')
    {
        return Arrays::toAssocBy($this->data, $by);
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Groups collection by column/property or callable.
     * 
     * @param mixed $by Column/property name or callable, returning generated column/property name. Default = 'id'.
     * @return Returns associative array of collections.
     */
    public function group($by = 'id')
    {
        $groups = Arrays::groupBy($this->data, $by);
        
        foreach ($groups as $key => $group) {
            $result[$key] = self::make($group);
        }
        
        return $result;
    }

    /**
     * Extracts non-null 'id' column/property values.
     */
    public function ids()
    {
        return $this->extract('id');
    }

    /**
     * Extracts non-null column/property values from collection.
     */
    public function extract($column)
    {
        $data = Arrays::extract($this->data, $column);
        return self::make($data);
    }
    
    public function any()
    {
        return !$this->empty();
    }

    public function empty()
    {
        return $this->count() == 0;
    }

    /**
     * Filters collection by column/property value or callable, then returns first item or null.
     */
    public function first($by = null, $value = null)
    {
        return $by
            ? Arrays::firstBy($this->data, $by, $value)
            : Arrays::first($this->data);
    }

    /**
     * Filters collection by column/property value or callable, then returns last item or null.
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
    public function where($by, $value = null)
    {
        $data = Arrays::filter($this->data, $by, $value);
        return self::make($data);
    }
    
    public function whereIn($column, $values)
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        $data = Arrays::filterIn($this->data, $column, $values);
        return self::make($data);
    }
    
    public function asc($column)
    {
        return $this->orderBy($column);
    }
    
    public function desc($column)
    {
        $data = Arrays::orderByDesc($this->data, $column, $dir);
        return self::make($data);
    }

    public function orderBy($column, $dir = null)
    {
        $data = Arrays::orderBy($this->data, $column, $dir);
        return self::make($data);
    }
    
    public function multiSort($sorts)
    {
        $data = Arrays::multiSort($this->data, $sorts);
        return self::make($data);
    }
	
	// ArrayAccess
	
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('$offset cannot be null.');
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
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
}
