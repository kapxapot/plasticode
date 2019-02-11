<?php

namespace Plasticode\Util;

class Sort
{
    const ASC = 'asc';
    const DESC = 'desc';
    
    const STRING = 'string';
    const NULL = 'null';
    const BOOL = 'bool';
    const DATE = 'date';
    
	/**
	 * Array multiple field sort.
	 * 
	 * Example config:
     *
	 * $sorts = [
	 *	'remote_online' => [ 'dir' => 'desc' ],
	 *	'priority' => [ 'dir' => 'desc' ],
	 *	'priority_game' => [ 'dir' => 'desc' ],
	 *	'remote_viewers' => [ 'dir' => 'desc' ],
	 *	'title' => [ 'dir' => 'asc', 'type' => 'string' ],
	 * ];
	 */
	public static function multi($array, $sorts)
	{
	    if (!empty($array)) {
    		usort($array, function($a, $b) use ($sorts) {
    		    foreach ($sorts as $field => $settings) {
    		    	$dir = $settings['dir'] ?? self::ASC;
    		    	$type = $settings['type'] ?? null;

    		    	$propA = self::getProperty($a, $field);
    		    	$propB = self::getProperty($b, $field);
    		    	
    		    	switch ($type) {
    		    	    case self::STRING:
    		    	        $cmp = strcasecmp($propA, $propB);
    		    	        break;
    		    	    
    		    	    case self::NULL:
    		    	        $a = is_null($propA);
    		    	        $b = is_null($propB);
    		    	        
    		    	        if ($a && $b || !$a && !$b) {
    		    	            $cmp = 0;
    		    	        } elseif ($a && !$b) {
    		    	            $cmp = -1;
    		    	        } elseif (!$a && $b) {
    		    	            $cmp = 1;
    		    	        }
    		    	        break;
    		    	    
    		    	    case self::BOOL:
    		    	        $a = $propA;
    		    	        $b = $propB;
    		    	        
    		    	        if ($a && $b || !$a && !$b) {
    		    	            $cmp = 0;
    		    	        } elseif ($a && !$b) {
    		    	            $cmp = 1;
    		    	        } elseif (!$a && $b) {
    		    	            $cmp = -1;
    		    	        }
    		    	        break;
    		    	    
    		    	    case self::DATE:
    		    	        $a = strtotime($propA);
    		    	        $b = strtotime($propB);

    		    	        $cmp = $a - $b;
    		    	        break;
    		    	    
    		    	    default:
    		    	        $cmp = $propA - $propB;
    		    	}

    		    	if ($cmp != 0) {
    			    	if ($dir == self::DESC) {
    			    		$cmp = -$cmp;
    			    	}
    			    	
    			    	return ($cmp > 0) ? 1 : -1;
    		    	}
    		    }
    		    
    		    return 0;
    		});
	    }
		
		return $array;
	}

    private static function getProperty($obj, $property)
    {
        return is_array($obj)
            ? $obj[$property]
            : $obj->{$property};
    }

	/**
	 * Sorts array by its field asc/desc.
	 * 
	 * @param string[] $array
	 * @param string $field
	 * @param string $dir 'asc' or 'desc'. null = 'asc'
	 * @param string $type Pass 'string' for string comparison
	 * 
	 * @return string[] Sorted array
	 */
	public static function by($array, $field, $dir = null, $type = null)
	{
		$sorts = [
			$field => [ 'dir' => $dir ?? self::ASC, 'type' => $type ],
		];
		
		return self::multi($array, $sorts);
	}
	
	/**
	 * Alias for by($array, $field)
	 */
	public static function asc($array, $field, $type = null)
	{
	    return self::by($array, $field, null, $type);
	}
	
	/**
	 * Shortcut for by($array, $field, 'desc')
	 */
	public static function desc($array, $field, $type = null)
	{
	    return self::by($array, $field, self::DESC, $type);
	}
	
	public static function byStr($array, $field, $dir = null)
	{
	    return self::by($array, $field, $dir, self::STRING);
	}
	
	public static function ascStr($array, $field)
	{
	    return self::byStr($array, $field);
	}
	
	public static function descStr($array, $field)
	{
	    return self::byStr($array, $field, self::DESC);
	}
}
