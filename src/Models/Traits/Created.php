<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

trait Created
{
    public function creator()
    {
        return static::getUser($this->createdBy);
    }

    public function createdAtIso()
    {
        return Date::iso($this->createdAt);
    }
}
