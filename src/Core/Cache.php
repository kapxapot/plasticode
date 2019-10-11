<?php

namespace Plasticode\Core;

class Cache
{
    private $cache;
    
    public function __construct()
    {
        $this->cache = [];
    }
    
    public function get(string $path)
    {
        return $this->cache[$path] ?? null;
    }
    
    public function set(string $path, $value)
    {
        $this->cache[$path] = $value;
    }
    
    public function exists(string $path) : bool
    {
        return array_key_exists($path, $this->cache);
    }
    
    public function delete(string $path)
    {
        if ($this->exists($path)) {
            unset($this->cache[$path]);
        }
    }
    
    public function getCached(string $path, \Closure $func)
    {
        if (!$this->exists($path)) {
            $this->set($path, $func());
        }
        
        return $this->get($path);
    }
}
