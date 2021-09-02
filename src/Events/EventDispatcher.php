<?php

namespace Plasticode\Events;

use Closure;
use Exception;
use Plasticode\Traits\LoggerAwareTrait;
use ReflectionFunction;
use ReflectionNamedType;

class EventDispatcher
{
    use LoggerAwareTrait;

    private EventDispatcherOptions $options;

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

    public function __construct()
    {
        $this->options = new EventDispatcherOptions();
    }

    /**
     * @return $this
     */
    public function withOptions(EventDispatcherOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Adds many handlers.
     */
    public function addHandlers(callable ...$handlers): void
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * Examines the handler and maps it to event class.
     */
    public function addHandler(callable $handler): void
    {
        $paramClass = $this->getHandlerParamClass($handler);

        $this->map[$paramClass] ??= [];
        $this->map[$paramClass][] = $handler;

        if ($this->options->logMappingMessages) {
            $this->log(get_class($handler) . ' mapped to ' . $paramClass);
        }
    }

    /**
     * Adds event to queue.
     */
    private function enqueue(Event $event): void
    {
        if ($event->isLooped()) {
            $this->log('[!] Loop found, enqueue aborted for ' . $event);
            return;
        }

        $this->queue[] = $event;

        $this->log('Queued ' . $event . ', queue size = ' . count($this->queue));
    }

    /**
     * Tries to take event from event queue.
     */
    private function dequeue(): ?Event
    {
        return array_shift($this->queue);
    }

    /**
     * Registers event and starts its handling.
     */
    public function dispatch(Event $event): void
    {
        $this->enqueue($event);

        if (!$this->processing) {
            $this->processNext();
        }
    }

    /**
     * Process next event in event queue.
     */
    private function processNext(): void
    {
        $event = $this->dequeue();

        if ($event) {
            $this->processEvent($event);
        }
    }

    private function processEvent(Event $event): void
    {
        if ($this->processing) {
            throw new Exception('Already processing an event!');
        }

        $this->processing = true;

        $this->log('Processing ' . $event);

        $eventClass = $event->getClass();

        $handlers = $this->map[$eventClass] ?? [];

        if (empty($handlers)) {
            $this->log('...no handlers found');
        }

        /** @var callable */
        foreach ($handlers as $handler) {
            $this->log('...invoking ' . get_class($handler));

            try {
                $handler($event);
            }
            catch (Exception $ex) {
                $this->log('[!] Handler invocation error: ' . $ex->getMessage());
            }
        }

        $this->log(
            '...finished, queue size = ' . count($this->queue)
        );

        $this->log('');

        $this->processing = false;

        $this->processNext();
    }

    private function getHandlerParamClass(callable $handler): ?string
    {
        $closure = Closure::fromCallable($handler);
        $function = new ReflectionFunction($closure);
        $params = $function->getParameters();

        if (empty($params)) {
            return null;
        }

        $param = $params[0];

        /** @var ReflectionNamedType|null $paramType */
        $paramType = $param->getType();

        return $paramType !== null
            ? $paramType->getName()
            : null;
    }
}
