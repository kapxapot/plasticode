<?php

namespace Plasticode;

class ObjectProxy
{
    private $object = null;
    private \Closure $initializer;
    private bool $initialized = false;

    public function __construct(\Closure $initializer)
    {
        $this->initializer = $initializer;
    }

    private function initialize() : void
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

        if (is_null($this->object)) {
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
