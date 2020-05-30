<?php

namespace Plasticode\Testing\EventHandlers;

use Plasticode\Events\EventDispatcher;
use Plasticode\Testing\Events\DataEvent;

class DataEventChainingHandler
{
    private \Closure $writer;
    private EventDispatcher $eventDispatcher;

    public function __construct(\Closure $writer, EventDispatcher $eventDispatcher)
    {
        $this->writer = $writer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(DataEvent $event) : void
    {
        ($this->writer)('Got data: ' . $event->getData() . '.');

        $this->eventDispatcher->dispatch(
            new DataEvent('chained', $event)
        );
    }
}
