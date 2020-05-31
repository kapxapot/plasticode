<?php

namespace Plasticode\Events;

class EventDispatcher
{
    private ?\Closure $logger = null;

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

    /**
     * @param callable[] $handlers
     */
    public function __construct(array $handlers, ?\Closure $logger = null)
    {
        $this->handlers = $handlers;
        $this->logger = $logger;
    }

    private function log(string $msg) : void
    {
        if ($this->logger) {
            ($this->logger)($msg);
        }
    }

    /**
     * Adds event to queue.
     */
    private function enqueue(Event $event) : void
    {
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

        $this->log('Processing ' . $event);

        $handlers = $this->getHandlers($eventClass);

        /** @var callable */
        foreach ($handlers as $handler) {
            $this->log('   invoking ' . get_class($handler));

            try {
                $handler($event);
            }
            catch (\Exception $ex) {
                $this->log('[!] Handler invocation error: ' . $ex->getMessage());
            }
        }

        $this->log(
            '   finished, queue size = ' . count($this->queue)
        );

        $this->log('');

        $this->processing = false;

        $this->processNext();
    }

    /**
     * @return callable[]
     */
    private function getHandlers(string $eventClass) : array
    {
        if (!array_key_exists($eventClass, $this->map)) {
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
        $map = [];

        /** @var callable */
        foreach ($this->handlers as $handler) {
            if ($this->isHandlerFor($handler, $eventClass)) {
                $this->log('   mapped ' . get_class($handler));

                $map[] = $handler;
            }
        }

        if (count($map) == 0) {
            $this->log('   no mapped handlers');
        }

        $this->map[$eventClass] = $map;
    }

    /**
     * Checks params of the callable.
     * If the 1st param's class = $eventClass, returns true.
     */
    private function isHandlerFor(callable $handler, string $eventClass) : bool
    {
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
