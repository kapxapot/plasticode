<?php

namespace Plasticode\Exceptions;

use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;

class ValidationException extends Exception implements PropagatedExceptionInterface
{
    /**
     * List of errors
     *
     * @var array
     */
    public $errors;
    
    /**
     * Creates ValidationException
     *
     * @param array $errors List of errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }
}
