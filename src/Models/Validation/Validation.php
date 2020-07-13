<?php

namespace Plasticode\Models\Validation;

use Plasticode\Models\Interfaces\ValidationInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

abstract class Validation implements ValidationInterface
{
    private ValidationRules $validationRules;

    public function __construct(ValidationRules $validationRules)
    {
        $this->validationRules = $validationRules;
    }

    protected function rule(string $name, bool $optional = false) : Validator
    {
        return $this->validationRules->get($name, $optional);
    }

    public abstract function getRules(array $data, $id = null) : array;
}
