<?php

namespace Plasticode\Testing\EventHandlers;

use Plasticode\Testing\Events\DataEvent;

class DataEventHandler
{
    private \Closure $writer;

    public function __construct(\Closure $writer)
    {
        $this->writer = $writer;
    }

    public function __invoke(DataEvent $event) : void
    {
        ($this->writer)('Got data: ' . $event->getData() . '.');
    }
}
