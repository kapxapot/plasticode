<?php

namespace Plasticode\Models;

class User extends DbModel
{
    // GETTERS - ONE
    
    public static function getByLogin($login)
    {
        return self::getBy(function ($q) use ($login) {
    		return $q
    		    ->whereAnyIs([
                    [ 'login' => $login ],
                    [ 'email' => $login ],
                ]);
        });
    }
    
    // PROPS
    
    public function displayName()
    {
        return $this->name ?? $this->login;
    }
    
    public function role()
    {
        return self::getRole($this->roleId);
    }
}
