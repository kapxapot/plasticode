<?php

namespace Plasticode\Validation\Interfaces;

use Respect\Validation\Validator;

interface ValidationInterface
{
    /**
     * Returns validation rules.
     *
     * @param mixed $id
     * @return Validator[]
     */
    function getRules(array $data, $id = null): array;
}
