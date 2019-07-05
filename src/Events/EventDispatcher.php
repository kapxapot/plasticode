<?php

namespace Plasticode\Events;

use Plasticode\Contained;
use Plasticode\Util\Classes;

class EventDispatcher extends Contained
{
    /**
     * Event processors
     *
     * @var array
     */
    private $processors = [];

    /**
     * Event -> processors mappings
     *
     * @var array
     */
    private $map = [];

    /**
     * Creates event dispatcher.
     *
     * @param object $container DI container
     * @param array $processors Event processors
     */
    public function __construct($container, array $processors)
    {
        parent::__construct($container);

        $this->processors = $processors;
    }

    private function log(string $msg) : void
    {
        $this->eventLog->info($msg);
    }

    public function dispatch(Event $event) : void
    {
        $this->log('Dispatching event ' . $event->toString());

        $eventClass = $event->getClass();
        $method = $this->getProcessMethod($eventClass);
        $processors = $this->getProcessors($eventClass);

        $queue = [];

        foreach ($processors as $processor) {
            $this->log('Invoking ' . $processor->getClass() . '->' . $method . '(' . $event->toString() . ')');

            $nextEventsIterator = $processor->{$method}($event);
            $nextEvents = iterator_to_array($nextEventsIterator);

            if (count($nextEvents) > 0) {
                $eventStr = implode(', ', array_map(function ($e) {
                    return $e->toString();
                }), $nextEvents);

                $this->log('Next events (' . count($nextEvents) . '): ' . $eventStr);
            }

            foreach ($nextEvents as $nextEvent) {
                $queue[] = $nextEvent->withParent($event);
            }
        }

        $this->log('Queue size: ' . count($queue));

        foreach ($queue as $queueEvent) {
            if (!$this->isLoop($queueEvent)) {
                $this->dispatch($queueEvent);
            }
        }

        $this->log('Finished dispatching event ' . $event->toString());
    }

    private function getProcessors(string $eventClass) : array
    {
        if (!array_key_exists($eventClass, $this->map)) {
            $this->log('No processor map found for ' . $eventClass);

            $this->mapEventClass($eventClass);
        }
        else {
            $this->log('Processor map found for ' . $eventClass);
        }

        return $this->map[$eventClass];
    }

    private function mapEventClass(string $eventClass) : void
    {
        $this->log('Building processor map for ' . $eventClass);

        $map = [];
        $methodName = $this->getProcessMethod($eventClass);

        foreach ($this->processors as $processor) {
            if (\method_exists($processor, $methodName)) {
                $this->log('Method ' . $methodName . ' found in processor ' . $processor->getClass());

                $map[] = $processor;
            }
        }

        $this->map[$eventClass] = $map;
    }

    private function getProcessMethod(string $eventClass)
    {
        return 'process' . Classes::shortName($eventClass);
    }

    /**
     * Looks for loops in the event chain.
     * 
     * Looks for the same event class with the same entity id.
     * 
     * @param Event $event
     * @return bool
     */
    private function isLoop(Event $event) : bool
    {
        $eventClass = $event->getClass();

        $cur = $event->getParent();

        while (!is_null($cur)) {
            $curClass = $cur->getClass();

            if ($curClass === $eventClass && $cur->getEntityId() === $event->getEntityId()) {
                $this->log('Loop found in event ' . $event->toString());

                return true;
            }

            $cur = $cur->getParent();
        }
        
        return false;
    }
}
