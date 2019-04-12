<?php

namespace Plasticode\Models;

class User extends DbModel
{
    // getters - one
    
    public static function getByLogin($login)
    {
        return self::query()
		    ->whereAnyIs([
                [ 'login' => $login ],
                [ 'email' => $login ],
            ])
            ->one();
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
