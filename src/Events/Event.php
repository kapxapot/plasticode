<?php

namespace Plasticode\Events;

abstract class Event
{
    /**
     * Parent event
     *
     * @var Event
     */
    private $parent;

    public function __construct(Event $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent() : ?Event
    {
        return $this->parent;
    }

    public function hasParent() : bool
    {
        return !is_null($this->parent);
    }
}
