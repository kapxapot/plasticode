<?php

namespace Plasticode\Testing\EventHandlers;

use Plasticode\Testing\Events\DbModelDummyEvent;

class DbModelDummyEventHandler
{
    private \Closure $writer;

    public function __construct(\Closure $writer)
    {
        $this->writer = $writer;
    }

    public function __invoke(DbModelDummyEvent $event) : void
    {
        ($this->writer)('Got dummy model: ' . $event->getDummyModel() . '.');
    }
}
