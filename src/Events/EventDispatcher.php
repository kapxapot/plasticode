<?php

namespace Plasticode\Events;

class EventDispatcher
{
    private ?\Closure $logger = null;

    /**
     * @var array<string, callable[]> Event class -> handlers mapping.
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
    public function __construct(array $handlers = [], ?\Closure $logger = null)
    {
        $this->logger = $logger;

        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * Examines the handler and maps it to event class.
     */
    public function addHandler(callable $handler) : void
    {
        $paramClass = $this->getHandlerParamClass($handler);

        if (!array_key_exists($paramClass, $this->map)) {
            $this->map[$paramClass] = [];
        }

        $this->map[$paramClass][] = $handler;

        $this->log(get_class($handler) . ' mapped to ' . $paramClass);
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

        $this->log('Processing ' . $event);

        $eventClass = $event->getClass();

        $handlers = $this->map[$eventClass] ?? [];

        if (empty($handlers)) {
            $this->log('   no handlers found');
        }

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

    private function getHandlerParamClass(callable $handler) : ?string
    {
        $closure = \Closure::fromCallable($handler);

        $rf = new \ReflectionFunction($closure);

        $params = $rf->getParameters();

        if (empty($params)) {
            return null;
        }

        $rp = $params[0];

        return $rp->getClass()->name;
    }

    /**
     * Looks for loops in the event chain.
     * 
     * Looks for the same event class with the same entity id.
     */
    private function isLoop(Event $event) : bool
    {
        $cur = $event->getParent();

        while ($cur) {
            if ($event->equals($cur)) {
                return true;
            }

            $cur = $cur->getParent();
        }

        return false;
    }
}
