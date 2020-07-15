<?php

namespace Plasticode\Exceptions;

use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;

class ValidationException extends Exception implements PropagatedExceptionInterface
{
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }
}
