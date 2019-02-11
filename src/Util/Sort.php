<?php

namespace Plasticode\Util;

class Sort
{
	/**
	 * OBSOLETE: use Sort::multi
	 */
	public function multiSort($array, $sorts)
	{
	    if (!empty($array)) {
    		usort($array, function($a, $b) use ($sorts) {
    		    foreach ($sorts as $field => $settings) {
    		    	$dir = $settings['dir'] ?? 'asc';
    		    	$str = ($settings['type'] ?? '') == 'string';
    		    	
    		    	$cmp = $str ? strcasecmp($a[$field], $b[$field]) : ($a[$field] - $b[$field]);
    		    	
    		    	if ($cmp != 0) {
    			    	if ($dir == 'desc') {
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
	    $sort = new Sort;
	    return $sort->multiSort($array, $sorts);
	}
	
	/**
	 * Sorts array by its field asc/desc.
	 * 
	 * @param string[] $array
	 * @param string $field
	 * @param string $dir 'asc' or 'desc'. null = 'asc'
	 * 
	 * @return string[] Sorted array
	 */
	public static function by($array, $field, $dir = null)
	{
		$sorts = [
			$field => [ 'dir' => $dir ?? 'asc' ],
		];
		
		return self::multi($array, $sorts);
	}
	
	/**
	 * Alias for by($array, $field)
	 */
	public static function asc($array, $field)
	{
	    return self::by($array, $field);
	}
	
	/**
	 * Shortcut for by($array, $field, 'desc')
	 */
	public static function desc($array, $field)
	{
	    return self::by($array, $field, 'desc');
	}
}
