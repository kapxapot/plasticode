<?php

namespace Plasticode\Exceptions\Interfaces;

interface HttpExceptionInterface
{
    /**
     * Returns HTTP error code
     *
     * @return integer
     */
    public function GetErrorCode() : int;
}
