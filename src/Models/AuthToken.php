<?php

namespace Plasticode\Models;

class AuthToken extends DbModel
{
    public static function getByToken($token)
    {
        return self::query()
            ->where('token', $token)
            ->one();
    }
    
    public function user()
    {
        return self::getUser($this->userId);
    }

    public function toString()
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
