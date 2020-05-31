<?php

namespace Plasticode\Testing\Events;

use Plasticode\Events\Event;

class DataEvent extends Event
{
    private $data;

    public function __construct($data, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function equals(?Event $event) : bool
    {
        return $event
            && $this->getClass() == $event->getClass()
            && $event instanceof self
            && $this->data === $event->getData();
    }

    public function __toString(): string
    {
        return parent::__toString() . ' (' . ($this->data ?? 'null') . ')';
    }
}
