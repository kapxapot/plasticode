<?php

namespace Plasticode\DI\Interfaces;

interface WritableContainerInterface
{
    /**
     * @param mixed $value
     */
    function set(string $id, $value): void;
}
