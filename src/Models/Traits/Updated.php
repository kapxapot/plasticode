<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Webmozart\Assert\Assert;

/**
 * @property integer|null $updatedBy
 */
trait Updated
{
    use UpdatedAt;

    protected ?User $updater = null;

    private bool $updaterInitialized = false;

    public function withUpdater(User $updater) : self
    {
        $this->updater = $updater;
        $this->updaterInitialized = true;

        return $this;
    }

    public function updater() : ?User
    {
        Assert::true($this->updaterInitialized);

        return $this->updater;
    }

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
