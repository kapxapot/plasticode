<?php

namespace Plasticode;

class ObjectProxy
{
    private $object = null;

    /** @var callable */
    private $initializer;

    private bool $initialized = false;

    public function __construct(callable $initializer)
    {
        $this->initializer = $initializer;
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->object = ($this->initializer)();
        $this->initialized = true;
    }

    public function __call(string $name, array $args)
    {
        $this->initialize();

        if ($this->object === null) {
            return null;
        }

        return $this->object->{$name}(...$args);
    }

    public function __invoke()
    {
        $this->initialize();

        return $this->object;
    }
}
