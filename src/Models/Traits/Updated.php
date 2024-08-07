<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;

/**
 * @property integer|null $updatedBy
 * @method User|null updater()
 * @method static withUpdater(User|callable|null $updater)
 */
trait Updated
{
    use UpdatedAt;

    protected string $updaterPropertyName = 'updater';

    public function isUpdatedBy(User $user): bool
    {
        return $this->updatedBy == $user->getId();
    }
}
