<?php

namespace Plasticode\Validation;

use Plasticode\Validation\Interfaces\ValidationInterface;
use Respect\Validation\Validator;

abstract class Validation implements ValidationInterface
{
    private ValidationRules $validationRules;

    public function __construct(ValidationRules $validationRules)
    {
        $this->validationRules = $validationRules;
    }

    protected function rule(string $name, bool $optional = false): Validator
    {
        return $this->validationRules->get($name, $optional);
    }

    abstract public function getRules(array $data, $id = null): array;
}
