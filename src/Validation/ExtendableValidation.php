<?php

namespace Plasticode\Validation;

use Plasticode\Validation\Interfaces\ExtendableValidationInterface;
use Plasticode\Validation\Interfaces\ValidationInterface;

abstract class ExtendableValidation extends Validation implements ExtendableValidationInterface
{
    /** @var ValidationInterface[] */
    private array $injectedValidations = [];

    public function getRules(array $data, $id = null): array
    {
        return array_merge(
            $this->getOwnRules($data, $id),
            ...array_map(
                fn (ValidationInterface $v) => $v->getRules($data, $id),
                $this->injectedValidations
            )
        );
    }

    abstract protected function getOwnRules(array $data, $id = null): array;

    public function extendWith(ValidationInterface $validation): self
    {
        $this->injectedValidations[] = $validation;

        return $this;
    }
}
