<?php

namespace Plasticode\Validation;

use Respect\Validation\Exceptions\ValidationException;

class ValidationResult
{
    /** @var array */
    private $errors;

    /**
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors ?? [];
    }

    public function errors() : array
    {
        return $this->errors;
    }

    public function isSuccess() : bool
    {
        return empty($this->errors);
    }
    
    public function isFail() : bool
    {
        return !$this->isFail();
    }

    /**
     * Throws ValidationException on errors.
     *
     * @throws ValidationException
     * @return void
     */
    public function throwOnFail() : void
    {
        if ($this->isFail()) {
            throw new ValidationException($this->errors);
        }
    }
}
