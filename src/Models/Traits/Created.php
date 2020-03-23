<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Plasticode\Util\Date;

/**
 * @property integer|null $createdBy
 * @property string|null $createdAt
 */
trait Created
{
    protected ?User $creator = null;

    public function createdBy() : ?int
    {
        return $this->createdBy;
    }

    public function withCreator(User $creator) : self
    {
        $this->creator = $creator;
        return $this;
    }

    public function creator() : ?User
    {
        return $this->creator;
    }

    /**
     * Sets or updates createdBy and creator.
     *
     * @param User $user
     * @return self
     */
    protected function stampCreator(User $user) : self
    {
        if ($this->createdBy > 0) {
            return $this;
        }

        $this->createdBy = $user->getId();

        return $this->withCreator($user);
    }

    public function createdAtIso() : string
    {
        return Date::iso($this->createdAt);
    }
}
