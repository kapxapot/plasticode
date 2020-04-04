<?php

namespace Plasticode\Validation;

use Plasticode\Exceptions\ValidationException;

class ValidationResult
{
    private array $errors;

    public function __construct(?array $errors = null)
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
        return !$this->isSuccess();
    }

    /**
     * Throws ValidationException on errors.
     *
     * @throws ValidationException
     */
    public function throwOnFail() : void
    {
        if ($this->isFail()) {
            throw new ValidationException($this->errors);
        }
    }
}
