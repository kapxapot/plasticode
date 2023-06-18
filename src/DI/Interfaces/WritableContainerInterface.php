<?php

namespace Plasticode\DI\Interfaces;

interface WritableContainerInterface
{
    /**
     * @param mixed $value
     */
    public function set(string $id, $value): void;
}
