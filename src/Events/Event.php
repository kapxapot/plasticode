<?php

namespace Plasticode\Events;

use Plasticode\Traits\GetClass;

abstract class Event
{
    use GetClass;

    /**
     * Parent event
     */
    private ?self $parent;

    public function __construct(?self $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent() : ?self
    {
        return $this->parent;
    }

    abstract public function equals(?self $event) : bool;

    public function __toString() : string
    {
        return $this->getClass();
    }
}
