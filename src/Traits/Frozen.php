<?php

namespace Plasticode\Traits;

use Plasticode\ObjectProxy;

/**
 * Elsa tribute.
 */
trait Frozen
{
    /**
     * Makes sure that the callable executes only once.
     */
    public function frozen(callable $func): ObjectProxy
    {
        return new ObjectProxy($func);
    }
}
