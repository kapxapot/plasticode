<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;

/**
 * @property integer|null $createdBy
 * @method User|null creator()
 * @method static withCreator(User|callable|null $creator)
 */
trait Created
{
    use CreatedAt;

    public function isCreatedBy(User $user) : bool
    {
        return $this->creator()
            ? $this->creator()->equals($user)
            : false;
    }

    /**
     * Sets or updates createdBy and creator.
     */
    protected function stampCreator(User $user) : self
    {
        if ($this->createdBy > 0) {
            return $this;
        }

        $this->createdBy = $user->getId();

        return $this->withCreator($user);
    }
}
