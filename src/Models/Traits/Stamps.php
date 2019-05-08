<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

trait Stamps
{
    use Created;

    public function updater()
    {
        return static::getUser($this->updatedBy);
    }

    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
    
    public function stamp()
    {
        $user = self::$auth->getUser();
        
        if ($user) {
            $this->createdBy = $this->createdBy ?? $user->id;
            $this->updatedBy = $user->id;
        }
        
        if ($this->isPersisted()) {
            $this->updatedAt = Date::dbNow();
        }
    }
}
