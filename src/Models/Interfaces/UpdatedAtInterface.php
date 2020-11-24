<?php

namespace Plasticode\Models\Interfaces;

/**
 * {@see DbModelInterface} wrapper with $updatedAt property.
 * 
 * @property string|null $updatedAt
 */
interface UpdatedAtInterface extends DbModelInterface
{
    function updatedAtIso() : string;
}
