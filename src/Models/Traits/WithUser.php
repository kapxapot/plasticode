<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\User;
use Webmozart\Assert\Assert;

/**
 * @property integer $userId
 */
trait WithUser
{
    protected ?User $user = null;

    private bool $userInitialized = false;

    public function withUser(User $user) : self
    {
        $this->user = $user;
        $this->userInitialized = true;

        return $this;
    }

    public function user() : User
    {
        Assert::true($this->userInitialized);

        return $this->user;
    }
}
