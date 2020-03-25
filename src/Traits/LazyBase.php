<?php

namespace Plasticode\Traits;

trait LazyBase
{
    protected static function getLazyFuncName() : string
    {
        [, , $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return $caller['function'];
    }
}
