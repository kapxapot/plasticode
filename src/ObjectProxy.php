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

        $initializer = $this->initializer;
        $this->object = $initializer();
        $this->initialized = true;
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        $this->initialize();

        if (is_null($this->object)) {
            return null;
        }

        return $this->object->{$name}(...$args);
    }
}
