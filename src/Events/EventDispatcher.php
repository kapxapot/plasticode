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
        $queue = [];

        $eventClass = $event->getClass();

        $this->log('Dispatching...');
        $this->log('...event: ' . $eventClass);
        $this->log('...entity: ' . $event->getEntity()->toString());

        $processors = $this->getProcessors($eventClass);
        $method = $this->getProcessMethod($eventClass);

        foreach ($processors as $processor) {
            $this->log('Invoking...');
            $this->log('...processor: ' . $processor->getClass());
            $this->log('...method: ' . $method);

            $nextEventsIterator = $processor->{$method}($event);
            //$nextEvents = iterator_to_array($nextEventsIterator);

            foreach ($nextEventsIterator as $nextEvent) {
                $this->log('Queueing...');
                $this->log('...event: ' . $nextEvent->getClass());
                $this->log('...entity: ' . $nextEvent->getEntity()->toString());
    
                $queue[] = $nextEvent->withParent($event);
            }
        }

        $this->log('Queue size: ' . count($queue));

        foreach ($queue as $queueEvent) {
            if (!$this->isLoop($queueEvent)) {
                $this->dispatch($queueEvent);
            }
            else {
                $this->log('[!] Loop found, aborting...');
                $this->log('...event: ' . $queueEvent->getClass());
                $this->log('...entity: ' . $queueEvent->getEntity()->toString());
            }
        }

        $this->log('Finished dispatching...');
        $this->log('...event: ' . $eventClass);
        $this->log('...entity: ' . $event->getEntity()->toString());
    }

    private function getProcessors(string $eventClass) : array
    {
        $this->log('Getting processors for ' . $eventClass);

        if (!array_key_exists($eventClass, $this->map)) {
            $this->log('...no processor map found');

            $this->mapEventClass($eventClass);
        }
        else {
            $this->log('...processor map found');
        }

        return $this->map[$eventClass];
    }

    private function mapEventClass(string $eventClass) : void
    {
        $this->log('...building processor map');

        $map = [];
        $methodName = $this->getProcessMethod($eventClass);

        foreach ($this->processors as $processor) {
            if (\method_exists($processor, $methodName)) {
                $this->log('...method ' . $methodName . ' found in processor ' . $processor->getClass());

                $map[] = $processor;
            }
        }

        if (count($map) == 0) {
            $this->log('...no processors found');
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
                return true;
            }

            $cur = $cur->getParent();
        }
        
        return false;
    }
}
