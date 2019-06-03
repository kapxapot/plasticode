<?php

namespace Plasticode\Models;

class User extends DbModel
{
    public static function getByLogin($login)
    {
        return self::query()
            ->whereAnyIs([
                [ 'login' => $login ],
                [ 'email' => $login ],
            ])
            ->one();
    }
    
    public function displayName()
    {
        return $this->name ?? $this->login;
    }
    
    public function role()
    {
        return self::getRole($this->roleId);
    }
    
    public function toString()
    {
        return '[' . $this->getId() . '] ' . $this->displayName();
    }
}
