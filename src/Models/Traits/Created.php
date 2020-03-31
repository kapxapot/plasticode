<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Webmozart\Assert\Assert;

/**
 * @property integer|null $createdBy
 */
trait Created
{
    use CreatedAt;

    protected ?User $creator = null;

    private bool $creatorInitialized = false;

    public function createdBy() : ?int
    {
        return $this->createdBy;
    }

    public function withCreator(User $creator) : self
    {
        $this->creator = $creator;
        $this->creatorInitialized = true;

        return $this;
    }

    public function creator() : ?User
    {
        Assert::true($this->creatorInitialized);

        return $this->creator;
    }

    public function isCreatedBy(User $user) : bool
    {
        return $this->creator()
            ? $this->creator()->equals($user)
            : false;
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
}
