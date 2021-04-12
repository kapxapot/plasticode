<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;

/**
 * Implements {@see \Plasticode\Models\Interfaces\CreatedInterface}.
 * 
 * @property integer|null $createdBy
 * @method User|null creator()
 * @method static withCreator(User|callable|null $creator)
 */
trait Created
{
    use CreatedAt;

    protected string $creatorPropertyName = 'creator';

    public function isCreatedBy(User $user): bool
    {
        return $this->createdBy == $user->getId();
    }
}
