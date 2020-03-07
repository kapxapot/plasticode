<?php

namespace Plasticode\Traits;

use Plasticode\Core\Cache;
use Plasticode\Core\Interfaces\CacheInterface;

trait LazyCache
{
    /**
     * Instance cache
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * Static cache
     *
     * @var CacheInterface
     */
    private static $staticCache;

    private static function getLazyFuncName() : string
    {
        list(, , $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return $caller['function'];
    }
    
    protected function lazy(\Closure $loader, string $name = null, bool $ignoreCache = false)
    {
        $name = $name ?? self::getLazyFuncName();
        
        return $this
            ->getCache()
            ->getCached($name, $loader, $ignoreCache);
    }
    
    protected function resetLazy(string $name) : void
    {
        $this
            ->getCache()
            ->delete($name);
    }
    
    protected static function staticLazy(\Closure $loader, string $name = null, bool $ignoreCache = false)
    {
        $name = $name ?? self::getLazyFuncName();
        
        return self::getStaticCache()
            ->getCached($name, $loader, $ignoreCache);
    }
    
    protected static function resetStaticLazy(string $name) : void
    {
        self::getStaticCache()
            ->delete($name);
    }

    private function getCache() : CacheInterface
    {
        if (is_null($this->cache)) {
            $this->cache = new Cache();
        }

        return $this->cache;
    }

    private static function getStaticCache(): CacheInterface
    {
        if (is_null(self::$staticCache)) {
            self::$staticCache = new Cache();
        }

        return self::$staticCache;
    }
}
