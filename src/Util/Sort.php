<?php

namespace Plasticode\Util;

class Sort {
	/*
	array multiple field sort
	
	example config:

	$sorts = [
		'remote_online' => [ 'dir' => 'desc' ],
		'priority' => [ 'dir' => 'desc' ],
		'priority_game' => [ 'dir' => 'desc' ],
		'remote_viewers' => [ 'dir' => 'desc' ],
		'title' => [ 'dir' => 'asc', 'type' => 'string' ],
	];
	*/
	public function multiSort($array, $sorts) {
		usort($array, function($a, $b) use ($sorts) {
		    foreach ($sorts as $field => $settings) {
		    	$dir = $settings['dir'];
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
		
		return $array;
	}
}
