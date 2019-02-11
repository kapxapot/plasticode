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
}
