<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\CacheInterface;

class Cache implements CacheInterface
{
    /** @var \ArrayAccess */
    private $cache;
    
    public function __construct(?\ArrayAccess $carrier = null)
    {
        $this->cache = $carrier ?? [];
    }
    
    public function get(string $path)
    {
        return $this->cache[$path] ?? null;
    }
    
    public function set(string $path, $value) : void
    {
        $this->cache[$path] = $value;
    }
    
    public function exists(string $path) : bool
    {
        return isset($this->cache[$path]);
    }
    
    public function delete(string $path) : void
    {
        if ($this->exists($path)) {
            unset($this->cache[$path]);
        }
    }
    
    public function getCached(string $path, \Closure $func, bool $forced = false)
    {
        if ($forced === true || !$this->exists($path)) {
            $this->set($path, $func());
        }
        
        return $this->get($path);
    }
}
