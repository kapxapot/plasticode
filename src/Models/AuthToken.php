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
    public function user() : User
    {
        return self::getUser($this->userId);
    }

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
