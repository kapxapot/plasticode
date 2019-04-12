<?php

namespace Plasticode\Models;

class AuthToken extends DbModel
{
    // GETTERS - ONE
    
    public static function getByToken($token)
    {
        return self::query()
            ->where('token', $token)
            ->one();
    }

    // PROPS
    
    public function user()
    {
        return self::getUser($this->userId);
    }
}
