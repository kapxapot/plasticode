<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Plasticode\Util\Date;

trait Stamps
{
    use Created;
    use Updated;

    /**
     * Updates createdBy/updatedBy/updatedAt.
     */
    public function stamp(?User $user = null) : void
    {
        if ($user) {
            $this->stampCreator($user);
            $this->stampUpdater($user);
        }

        if ($this->isPersisted()) {
            $this->updatedAt = Date::dbNow();
        }
    }

    public abstract function isPersisted() : bool;
}
