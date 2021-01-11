<?php

namespace Plasticode\Repositories\Interfaces\Generic;

interface FieldValidatingRepositoryInterface extends RepositoryInterface
{
    /**
     * @param mixed $value
     */
    function isValidField(string $field, $value, ?int $exceptId = null): bool;
}
