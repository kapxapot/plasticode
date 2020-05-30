<?php

namespace Plasticode\Events;

use Psr\Log\LoggerInterface;

class EventDispatcher
{
    private LoggerInterface $log;

    /**
     * @var callable[] Event handlers.
     */
    private array $handlers;

    /**
     * @var array<string, callable> Event class -> handlers mapping.
     */
    private array $map = [];

    /**
     * @var Event[] Event queue.
     */
    private array $queue = [];

    /**
     * Is the event dispatcher processing an event currently.
     */
    private bool $processing = false;

    public function __construct(
        LoggerInterface $log,
        array $handlers
    )
    {
        $this->log = $log;
        $this->handlers = $handlers;
    }

    private function log(string $msg) : void
    {
        $this->log->info($msg);
    }

    /**
     * Adds event to queue.
     */
    private function enqueue(Event $event) : void
    {
        // checking for loop
        if ($this->isLoop($event)) {
            $this->log('[!] Loop found, enqueue aborted for ' . $event);
            return;
        }

        $this->queue[] = $event;

        $this->log('Queued ' . $event . ', queue size = ' . count($this->queue));
    }

    /**
     * Tries to take event from event queue.
     */
    private function dequeue() : ?Event
    {
        return array_shift($this->queue);
    }

    /**
     * Registers event and starts its handling.
     */
    public function dispatch(Event $event) : void
    {
        $this->enqueue($event);

        if (!$this->processing) {
            $this->processNext();
        }
    }

    /**
     * Process next event in event queue.
     */
    private function processNext() : void
    {
        $event = $this->dequeue();

        if ($event) {
            $this->processEvent($event);
        }
    }

    private function processEvent(Event $event) : void
    {
        if ($this->processing) {
            throw new \Exception('Already processing an event!');
        }

        $this->processing = true;

        $eventClass = $event->getClass();

        $this->log('Processing...');
        $this->log('   event: ' . $eventClass);
        $this->log('   entity: ' . $event->getEntity()->toString());

        $handlers = $this->getHandlers($eventClass);

        /** @var callable */
        foreach ($handlers as $handler) {
            $this->log('Invoking handler: ' . get_class($handler));

            try {
                $handler($event);
            }
            catch (\Exception $ex) {
                $this->log('[!] Handler invocation error: ' . $ex->getMessage());
            }
        }

        $this->log('Finished processing ' . $eventClass);
        $this->log('Queue size = ' . count($this->queue));
        $this->log('');

        $this->processing = false;

        $this->processNext();
    }

    /**
     * @return callable[]
     */
    private function getHandlers(string $eventClass) : array
    {
        $this->log('Getting handlers for ' . $eventClass);

        if (!array_key_exists($eventClass, $this->map)) {
            $this->log('   no handler map found');

            $this->mapEventClass($eventClass);
        } else {
            $this->log(
                '   handler map found ('
                . count($this->map[$eventClass])
                . ' handlers)'
            );
        }

        return $this->map[$eventClass];
    }

    private function mapEventClass(string $eventClass) : void
    {
        $this->log('   building handler map');

        $map = [];

        /** @var callable */
        foreach ($this->handlers as $handler) {
            if ($this->isHandlerFor($handler, $eventClass)) {
                $this->log('   found handler: ' . get_class($handler));

                $map[] = $handler;
            }
        }

        if (count($map) == 0) {
            $this->log('   no handlers found');
        }

        $this->map[$eventClass] = $map;
    }

    private function isHandlerFor(callable $handler, string $eventClass) : bool
    {
        // check params of the callable
        // if the 1st param's class = $eventClass, return true
        $closure = \Closure::fromCallable($handler);

        $rf = new \ReflectionFunction($closure);

        $params = $rf->getParameters();

        if (empty($params)) {
            return false;
        }

        $rp = $params[0];

        return $rp->getClass()->name === $eventClass;
    }

    /**
     * Looks for loops in the event chain.
     * 
     * Looks for the same event class with the same entity id.
     */
    private function isLoop(Event $event) : bool
    {
        $eventClass = $event->getClass();

        $cur = $event->getParent();

        while ($cur) {
            $curClass = $cur->getClass();

            if ($curClass === $eventClass && $cur->getEntityId() === $event->getEntityId()) {
                return true;
            }

            $cur = $cur->getParent();
        }

        return false;
    }
}
