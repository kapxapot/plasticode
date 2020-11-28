<?php

namespace Plasticode\Repositories\Interfaces\Basic;

interface FieldValidatingRepositoryInterface extends RepositoryInterface
{
    /**
     * @param mixed $value
     */
    function isValidField(string $field, $value, ?int $exceptId = null) : bool;
}
