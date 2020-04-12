<?php

namespace Plasticode\Traits;

use Plasticode\ObjectProxy;

trait Once
{
    public function once(\Closure $func) : ObjectProxy
    {
        return new ObjectProxy($func);
    }
}
