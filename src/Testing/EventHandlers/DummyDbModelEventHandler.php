<?php

namespace Plasticode\Testing\EventHandlers;

use Plasticode\Testing\Events\DummyDbModelEvent;

class DummyDbModelEventHandler
{
    private \Closure $writer;

    public function __construct(\Closure $writer)
    {
        $this->writer = $writer;
    }

    public function __invoke(DummyDbModelEvent $event) : void
    {
        ($this->writer)('Got dummy model: ' . $event->getDummyModel() . '.');
    }
}
