<?php

namespace Plasticode\Models\Interfaces;

use Respect\Validation\Validator;

interface ValidationInterface
{
    /**
     * Returns validation rules.
     *
     * @param array $data
     * @param integer|string|null $id
     * @return Validator[]
     */
    function getRules(array $data, $id = null) : array;
}
