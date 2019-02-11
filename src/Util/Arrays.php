<?php

namespace Plasticode\Util;

class Arrays
{
    /**
     * Groups array by column or callable.
     * 
     * @param array $array
     * @param mixed $by Column name or callable, returning generated column name. Default = 'id'.
     */
    public static function groupBy($array, $by = 'id')
    {
        $result = [];
        foreach ($array as $element) {
            $key = is_callable($by)
                ? $by($element)
                : $element[$by];
            
            $result[$key][] = $element;
        }
        
        return $result;
    }

    /**
     * Converts array to associative array by column or callable.
     * Selector must be unique, otherwise only first element is taken, others are discarded.
     * 
     * @param array $array
     * @param mixed $by Column name or callable, returning generated column name. Default = 'id'.
     */
    public static function toAssocBy($array, $by = 'id')
    {
        $groups = self::groupBy($array, $by);
        
        array_walk($groups, function (&$item, $key) {
            return $item = $item[0];
        });
        
        return $groups;
    }
    
    /**
     * Returns distinct values from array grouped by selector ('id' by default).
     * 
     * @param array $array
     * @param mixed $by Column name or callable, returning generated column name. Default = 'id'.
     */
    public static function distinctBy($array, $by = 'id')
    {
        return array_values(self::toAssocBy($array, $by));
    }
    
    /**
     * Extracts non-null column values from array.
     */
    public static function extract($array, $column)
    {
        if ($array === null) {
            return null;
        }
        
        return array_filter(array_column($array, $column), function ($item) {
            return $item !== null;
        });
    }
    
    /**
     * Extracts non-null 'id' column values.
     */
    public static function extractIds($array)
    {
        return self::extract($array, 'id');
    }
    
    /**
     * Filters array by column value or callable.
     */
    public static function filter($array, $by, $value = null)
    {
        if ($array === null) {
            return null;
        }
        
        return array_filter($array, function ($item) use ($by, $value) {
            return is_callable($by)
                ? $by($item)
                : $item[$by] == $value;
        });
    }
    
    /**
     * Returns first item from array or null.
     */
    public static function first($array)
    {
        return !empty($array) ? reset($array) : null;
    }
    
    /**
     * Returns last item from array or null.
     */
    public static function last($array)
    {
        return !empty($array) ? end($array) : null;
    }
    
    /**
     * Filters array by column value or callable, then returns first item or null.
     */
    public static function firstBy($array, $by, $value = null)
    {
        $filtered = self::filter($array, $by, $value);
        return self::first($filtered);
    }
    
    /**
     * Filters associative array by provided key set.
     * If key is absent, it is ignored.
     */
    public static function filterKeys($array, $keys)
    {
        $result = [];
        
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $result[$key] = $array[$key];
            }
        }
        
        return $result;
    }
}
