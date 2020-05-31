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

        $this->assertCount(18, $log);
    }

    public function testClosures() : void
    {
        $output = [];
        $log = [];

        $logger = function (string $msg) use (&$log) {
            $log[] = $msg;
        };

        $dispatcher = new EventDispatcher(
            [
                function (EmptyEvent $event) use (&$output) {
                    $output[] = 'Got an empty event in closure.';
                },
                function (DataEvent $event) use (&$output) {
                    $output[] =
                        'Got data in closure: '
                        . $event->getData()
                        . '.';
                },
                function (DummyDbModelEvent $event) use (&$output) {
                    $output[] =
                        'Got dummy model in closure: '
                        . $event->getDummyModel()
                        . '.';
                }
            ],
            $logger
        );

        $dispatcher->dispatch(
            new EmptyEvent()
        );

        $dispatcher->dispatch(
            new DataEvent('red')
        );

        $dummyModel = new DummyDbModel(['id' => 456]);

        $dispatcher->dispatch(
            new DummyDbModelEvent($dummyModel)
        );

        $this->assertCount(3, $output);

        $this->assertEquals(
            [
                'Got an empty event in closure.',
                'Got data in closure: red.',
                'Got dummy model in closure: ' . $dummyModel . '.',
            ],
            $output
        );

        $this->assertCount(18, $log);
    }
}
