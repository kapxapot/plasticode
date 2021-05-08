<?php

namespace Plasticode\Models\Interfaces;

interface EquatableInterface
{
    /**
     * @param static|null $obj
     */
    public function equals(?self $obj): bool;
}
