<?php

namespace Plasticode\Traits;

use Plasticode\ObjectProxy;

/**
 * Elsa tribute.
 */
trait Frozen
{
    /**
     * Makes sure that the closure executes only once.
     */
    public function frozen(\Closure $func) : ObjectProxy
    {
        return new ObjectProxy($func);
    }
}
