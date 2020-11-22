<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

/**
 * Implements {@see Plasticode\Models\Interfaces\UpdatedAtInterface}.
 * 
 * @property string|null $updatedAt
 */
trait UpdatedAt
{
    public function updatedAtIso() : string
    {
        return Date::iso($this->updatedAt);
    }
}
