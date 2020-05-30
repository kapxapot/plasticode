<?php

namespace Plasticode\Tests\Events;

use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;
use Plasticode\Testing\Dummies\DummyDbModel;
use Plasticode\Testing\EventHandlers\DataEventHandler;
use Plasticode\Testing\EventHandlers\DummyDbModelEventHandler;
use Plasticode\Testing\EventHandlers\EmptyEventHandler;
use Plasticode\Testing\Events\DataEvent;
use Plasticode\Testing\Events\DummyDbModelEvent;
use Plasticode\Testing\Events\EmptyEvent;

final class EventDispatcherTest extends TestCase
{
    public function testHandlers() : void
    {
        $output = [];
        $log = [];

        $writer = function ($entry) use (&$output) {
            $output[] = $entry;
        };

        $logger = function (string $msg) use (&$log) {
            $log[] = $msg;
        };

        $dispatcher = new EventDispatcher(
            [
                new EmptyEventHandler($writer),
                new DataEventHandler($writer),
                new DummyDbModelEventHandler($writer)
            ],
            $logger
        );

        $dispatcher->dispatch(
            new EmptyEvent()
        );

        $dispatcher->dispatch(
            new DataEvent('blue')
        );

        $dummyModel = new DummyDbModel(['id' => 123]);

        $dispatcher->dispatch(
            new DummyDbModelEvent($dummyModel)
        );

        $this->assertCount(3, $output);

        $this->assertEquals(
            [
                'Got an empty event.',
                'Got data: blue.',
                'Got dummy model: ' . $dummyModel . '.',
            ],
            $output
        );
    }
}
