<?php

namespace Plasticode\Testing\Events;

use Plasticode\Events\Event;

class EmptyEvent extends Event
{
    public function equals(?Event $event) : bool
    {
        return $event && $this->getClass() === $event->getClass();
    }
}
