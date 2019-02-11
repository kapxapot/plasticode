<?php

namespace Plasticode\Core;

class Cache
{
	private $cache;
	
	public function __construct()
	{
		$this->cache = [];
	}
	
	public function get($path)
	{
		return $this->cache[$path] ?? null;
	}
	
	public function set($path, $value)
	{
		$this->cache[$path] = $value;
	}
	
	public function exists($path)
	{
	    return array_key_exists($path, $this->cache);
	}
	
	public function delete($path)
	{
	    if ($this->exists($path)) {
	        unset($this->cache[$path]);
	    }
	}
}
