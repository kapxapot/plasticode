<?php

namespace Plasticode\Exceptions;

use Plasticode\Exceptions\Interfaces\PropagatedExceptionInterface;
use Webmozart\Assert\Assert;

class ValidationException extends Exception implements PropagatedExceptionInterface
{
    /** @var array<string, string[]> */
    private array $errors;

    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array<string, string[]>
     */
    public function errors() : array
    {
        return $this->errors;
    }

    public function firstError() : string
    {
        Assert::notEmpty($this->errors);

        $key = array_key_first($this->errors);
        $element = $this->errors[$key];

        Assert::notEmpty($element);

        return $element[0];
    }
}
