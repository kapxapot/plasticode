<?php

namespace Plasticode\Models;

class AuthToken extends DbModel
{
    public static function getByToken($token) : ?self
    {
        return self::query()
            ->where('token', $token)
            ->one();
    }
    
    public function user() : User
    {
        return self::getUser($this->userId);
    }

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
