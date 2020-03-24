<?php

namespace Plasticode\Models;

/**
 * @property integer $id
 * @property integer $userId
 * @property string $token
 * @property string|null $expiresAt
 * @property string $createdAt
 * @property string $updatedAt
 */
class AuthToken extends DbModel
{
    private User $user;

    public function withUser(User $user) : self
    {
        $this->user = $user;
        return $this;
    }

    public function userId() : int
    {
        return $this->userId;
    }

    public function user() : User
    {
        return $this->user;
    }

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
