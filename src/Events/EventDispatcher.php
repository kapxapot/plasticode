<?php

namespace Plasticode\Events;

class EventDispatcher
{
    private $processors = [];

    public function registerProcessor(string $eventClass, string $processorClass)
    {
        if (!array_key_exists($eventClass, $this->processors)) {
            $this->processors[$eventClass] = [];
        }
        elseif (in_array($processorClass, $this->processors[$eventClass])) {
            return;
        }

        $this->processors[$eventClass][] = $processorClass;
    }

    public function dispatch(Event $event, Event $parentEvent = null)
    {
        $eventClass = 
    }
}
