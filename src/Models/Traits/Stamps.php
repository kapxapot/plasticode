<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Plasticode\Util\Date;

trait Stamps
{
    use Created, Updated;

    public function stamp(?User $user)
    {
        if ($user) {
            $this->createdBy = $this->createdBy ?? $user->getId();
            $this->updatedBy = $user->getId();
        }
        
        if ($this->isPersisted()) {
            $this->updatedAt = Date::dbNow();
        }
    }
}
