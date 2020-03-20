<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Plasticode\Util\Date;

/**
 * @property integer|null $updatedBy
 * @property string|null $updatedAt
 */
trait Updated
{
    protected ?User $updater = null;

    public function withUpdater(User $updater) : self
    {
        $this->updater = $updater;
        return $this;
    }

    public function updater() : ?User
    {
        return $this->updater;
    }

    /**
     * Sets or updates updatedBy and updater.
     *
     * @param User $user
     * @return self
     */
    protected function stampUpdater(User $user) : self
    {
        $this->updatedBy = $user->getId();

        return $this->withUpdater($user);
    }

    public function updatedAtIso() : string
    {
        return Date::iso($this->updatedAt);
    }
}
