<?php

namespace Plasticode\Testing\EventHandlers;

use Plasticode\Testing\Events\EmptyEvent;

class EmptyEventHandler
{
    private \Closure $writer;

    public function __construct(\Closure $writer)
    {
        $this->writer = $writer;
    }

    public function __invoke(EmptyEvent $event) : void
    {
        ($this->writer)('Got an empty event.');
    }
}
