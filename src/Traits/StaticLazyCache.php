<?php

namespace Plasticode\Traits;

use Plasticode\Core\Interfaces\CacheInterface;

trait StaticLazyCache
{
    use LazyBase;

    protected static function staticLazy(
        \Closure $loader,
        string $name = null,
        bool $ignoreCache = false
    )
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

    protected abstract static function getStaticCache(): CacheInterface;
}
