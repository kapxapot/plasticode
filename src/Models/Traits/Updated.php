<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;

/**
 * @property integer|null $updatedBy
 * @method User updater()
 * @method static withUpdater(User|callable $updater)
 */
trait Updated
{
    use UpdatedAt;

    public function isUpdatedBy(User $user) : bool
    {
        return $this->updater()
            ? $this->updater()->equals($user)
            : false;
    }

    /**
     * Sets or updates updatedBy and updater.
     */
    protected function stampUpdater(User $user) : self
    {
        $this->updatedBy = $user->getId();

        return $this->withUpdater($user);
    }
}
