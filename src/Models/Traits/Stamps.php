<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

trait Stamps
{
    public function creator()
    {
        return static::getUser($this->createdBy);
    }
    
    public function updater()
    {
        return static::getUser($this->updatedBy);
    }
    
    public function createdAtIso()
    {
        return Date::iso($this->createdAt);
    }
    
    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
}
