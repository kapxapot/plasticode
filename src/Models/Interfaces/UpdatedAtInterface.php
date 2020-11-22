<?php

namespace Plasticode\Models\Interfaces;

/**
 * DbModel interface wrapper with $updatedAt property.
 * 
 * @property string|null $updatedAt
 */
interface UpdatedAtInterface extends DbModelInterface
{
    function updatedAtIso() : string;
}
