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

    /**
     * Looks for loops in the event hierarchy chain.
     * 
     * Looks for the same event using equals() method.
     */
    public function isLooped() : bool
    {
        return $this->hasAncestor($this);
    }

    public function hasAncestor(self $event) : bool
    {
        return $this->hasParent() && (
            $this->parent->equals($event)
            || $this->parent->hasAncestor($event)
        );
    }

    public function getParent() : ?self
    {
        return $this->parent;
    }

    public function hasParent() : bool
    {
        return $this->parent !== null;
    }

    abstract public function equals(?self $event) : bool;

    public function __toString() : string
    {
        return $this->getClass();
    }
}
