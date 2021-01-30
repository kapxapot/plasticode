<?php

namespace Plasticode\Tests\Events;

use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;
use Plasticode\Testing\Dummies\DbModelDummy;
use Plasticode\Testing\EventHandlers\DataEventHandler;
use Plasticode\Testing\EventHandlers\DbModelDummyEventHandler;
use Plasticode\Testing\EventHandlers\EmptyEventHandler;
use Plasticode\Testing\Events\DataEvent;
use Plasticode\Testing\Events\DbModelDummyEvent;
use Plasticode\Testing\Events\EmptyEvent;

final class EventDispatcherTest extends TestCase
{
    public function testHandlers() : void
    {
        $output = [];

        $writer = function ($entry) use (&$output) {
            $output[] = $entry;
        };

        $dispatcher = new EventDispatcher();

        $dispatcher->addHandlers(
            new EmptyEventHandler($writer),
            new DataEventHandler($writer),
            new DbModelDummyEventHandler($writer)
        );

        $dispatcher->dispatch(
            new EmptyEvent()
        );

        $dispatcher->dispatch(
            new DataEvent('blue')
        );

        $dummyModel = new DbModelDummy(['id' => 123]);

        $dispatcher->dispatch(
            new DbModelDummyEvent($dummyModel)
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

    public function testClosures() : void
    {
        $output = [];

        $dispatcher = new EventDispatcher();

        $dispatcher->addHandlers(
            function (EmptyEvent $event) use (&$output) {
                $output[] = 'Got an empty event in closure.';
            },
            function (DataEvent $event) use (&$output) {
                $output[] =
                    'Got data in closure: '
                    . $event->getData()
                    . '.';
            },
            function (DbModelDummyEvent $event) use (&$output) {
                $output[] =
                    'Got dummy model in closure: '
                    . $event->getDummyModel()
                    . '.';
            }
        );

        $dispatcher->dispatch(
            new EmptyEvent()
        );

        $dispatcher->dispatch(
            new DataEvent('red')
        );

        $dummyModel = new DbModelDummy(['id' => 456]);

        $dispatcher->dispatch(
            new DbModelDummyEvent($dummyModel)
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
    }

    public function testChainedEvents() : void
    {
        $output = [];

        $dispatcher = new EventDispatcher();

        $dispatcher->addHandler(
            function (DataEvent $event) use (&$output, $dispatcher) {
                $data = $event->getData();

                $output[] = 'Got data in closure: ' . $data . '.';

                if ($data == 'original') {
                    $dispatcher->dispatch(
                        new DataEvent('chained', $event)
                    );
                }
            },
        );

        $dispatcher->dispatch(
            new DataEvent('original')
        );

        $this->assertCount(2, $output);

        $this->assertEquals(
            [
                'Got data in closure: original.',
                'Got data in closure: chained.',
            ],
            $output
        );
    }

    public function testLoop() : void
    {
        $output = [];

        $dispatcher = new EventDispatcher();

        $dispatcher->addHandler(
            function (DataEvent $event) use (&$output, $dispatcher) {
                $data = $event->getData();

                $output[] = 'Got data in closure: ' . $data . '.';

                if ($data == 'original') {
                    $dispatcher->dispatch(
                        new DataEvent('chained', $event)
                    );
                }

                if ($data == 'chained') {
                    // this is THE LOOP
                    $dispatcher->dispatch(
                        new DataEvent('original', $event)
                    );
                }
            },
        );

        $dispatcher->dispatch(
            new DataEvent('original')
        );

        $this->assertCount(2, $output);

        $this->assertEquals(
            [
                'Got data in closure: original.',
                'Got data in closure: chained.',
            ],
            $output
        );
    }
}
