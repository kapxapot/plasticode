<?php

namespace Plasticode\Traits;

use Plasticode\Core\Interfaces\CacheInterface;

trait LazyCache
{
    use LazyBase;

    protected function lazy(
        \Closure $loader,
        string $name = null,
        bool $ignoreCache = false
    )
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

    protected abstract function getCache() : CacheInterface;
}