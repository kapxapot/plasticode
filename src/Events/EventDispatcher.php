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
     * Event queue
     *
     * @var array
     */
    private $queue = [];

    /**
     * Creates event dispatcher
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

    /**
     * Adds event to event queue
     *
     * @param Event $event
     * @return void
     */
    private function enqueue(Event $event) : void
    {
        // checking for loop
        if ($this->isLoop($event)) {
            $this->log('[!] Loop found, enqueue aborted for ' . $event->toString());
            return;
        }

        $this->queue[] = $event;

        $this->log('Queued ' . $event->toString() . ', queue size = ' . count($this->queue));
    }

    /**
     * Tries to take event from event queue
     *
     * @return Event|null
     */
    private function dequeue() : ?Event
    {
        return array_shift($this->queue);
    }

    /**
     * Entry point for new event processing
     *
     * @param Event $event
     * @return void
     */
    public function dispatch(Event $event) : void
    {
        $this->processEvent($event);
    }

    private function processEvent(Event $event) : void
    {
        $eventClass = $event->getClass();

        $this->log('Processing...');
        $this->log('   event: ' . $eventClass);
        $this->log('   entity: ' . $event->getEntity()->toString());

        $processors = $this->getProcessors($eventClass);
        $method = $this->getProcessMethod($eventClass);

        foreach ($processors as $processor) {
            $this->log('Invoking...');
            $this->log('   processor: ' . $processor->getClass());
            $this->log('   method: ' . $method);

            try {
                $nextEventsIterator = $processor->{$method}($event);
    
                foreach ($nextEventsIterator as $nextEvent) {
                    $eventWithParent = $nextEvent->withParent($event);

                    $this->enqueue($eventWithParent);
                }
            }
            catch (\Exception $ex) {
                $this->log('[!] Event processing error...');
                $this->log('   message: ' . $ex->getMessage());
            }
        }

        $this->log('Finished processing ' . $eventClass);
        $this->log('Queue size = ' . count($this->queue));
        $this->log('');

        $this->processNext();
    }

    /**
     * Process next event in event queue
     *
     * @return void
     */
    private function processNext() : void
    {
        $event = $this->dequeue();

        if (!is_null($event)) {
            $this->processEvent($event);
        }
    }

    private function getProcessors(string $eventClass) : array
    {
        $this->log('Getting processors for ' . $eventClass);

        if (!array_key_exists($eventClass, $this->map)) {
            $this->log('   no processor map found');

            $this->mapEventClass($eventClass);
        }
        else {
            $this->log('   processor map found (' . count($this->map[$eventClass]) . ' processors)');
        }

        return $this->map[$eventClass];
    }

    private function mapEventClass(string $eventClass) : void
    {
        $this->log('   building processor map');

        $map = [];
        $methodName = $this->getProcessMethod($eventClass);

        foreach ($this->processors as $processor) {
            if (\method_exists($processor, $methodName)) {
                $this->log('   method ' . $methodName . ' found in processor ' . $processor->getClass());

                $map[] = $processor;
            }
        }

        if (count($map) == 0) {
            $this->log('   no processors found');
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
