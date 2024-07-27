<?php

namespace Plasticode\Exceptions\Interfaces;

interface HttpExceptionInterface
{
    /**
     * Returns HTTP error code.
     */
    public function getErrorCode(): int;
}
