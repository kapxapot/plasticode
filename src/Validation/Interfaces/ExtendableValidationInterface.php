<?php

namespace Plasticode\Validation\Interfaces;

interface ExtendableValidationInterface extends ValidationInterface
{
    /**
     * Extend with more validation rules.
     *
     * @return $this
     */
    function extendWith(ValidationInterface $validation): self;
}
