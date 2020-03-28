<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

/**
 * @property string|null $createdAt
 */
trait CreatedAt
{
    public function createdAtIso() : string
    {
        return Date::iso($this->createdAt);
    }
}
