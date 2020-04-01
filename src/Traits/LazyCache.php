<?php

namespace Plasticode\Traits;

use Plasticode\Core\Interfaces\CacheInterface;

trait LazyCache
{
    use LazyBase;

    /**
     * @return mixed
     */
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

    abstract protected function getCache() : CacheInterface;
}
