<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;

/**
 * @property integer|null $updatedBy
 */
trait Updated
{
    use UpdatedAt;

    protected ?User $updater = null;

    public function updatedBy() : ?int
    {
        return $this->updatedBy;
    }

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
}
