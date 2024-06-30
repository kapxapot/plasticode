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
            && $event instanceof self
            && $this->getClass() === $event->getClass()
            && $this->data === $event->getData();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s (%s)',
            parent::__toString(),
            $this->data ?? 'null' // does this make sense if data is an object?
        );
    }
}
