<?php

namespace Plasticode\Events;

use Plasticode\Util\Classes;

class EventDispatcher
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
     * @param array $processors Event processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    public function dispatch(Event $event) : void
    {
        $eventClass = get_class($event);
        $method = $this->getProcessMethod($eventClass);
        $processors = $this->getProcessors($eventClass);

        $queue = [];

        foreach ($processors as $processor) {
            $nextEvents = $processor->{$method}($event);

            foreach ($nextEvents as $nextEvent) {
                $queue[] = $nextEvent;
            }
        }

        foreach ($queue as $queueEvent) {
            if (!$this->isLoop($queueEvent)) {
                $this->dispatch($queueEvent);
            }
        }
    }

    private function getProcessors(string $eventClass) : array
    {
        if (!array_key_exists($eventClass, $this->map)) {
            $this->mapEventClass($eventClass);
        }

        return $this->map[$eventClass];
    }

    private function mapEventClass(string $eventClass) : void
    {
        $methodName = $this->getProcessMethod($eventClass);

        foreach ($this->processors as $processor) {
            if (\method_exists($processor, $methodName)) {
                $this->addMapping($eventClass, $processor);
            }
        }
    }

    private function getProcessMethod(string $eventClass)
    {
        return "process" . Classes::shortName($eventClass);
    }

    /**
     * Looks for loops in the event chain.
     * 
     * Temporarily just looks for the same event class.
     * 
     * To do: Should check entity id too.
     *
     * @param Event $event
     * @return bool
     */
    private function isLoop(Event $event) : bool
    {
        $eventClass = get_class($event);

        $cur = $event->getParent();

        while (!is_null($cur)) {
            $curClass = get_class($cur);

            // should check entities' ids here too
            if ($curClass === $eventClass) {
                return true;
            }

            $cur = $cur->getParent();
        }
        
        return false;
    }
}
