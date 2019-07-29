<?php

namespace Plasticode\Models;

class User extends DbModel
{
    public static function getByLogin($login) : ?self
    {
        return self::query()
            ->whereAnyIs(
                [
                    ['login' => $login],
                    ['email' => $login],
                ]
            )
            ->one();
    }
    
    public function displayName() : string
    {
        return $this->name ?? $this->login;
    }
    
    public function role() : Role
    {
        return self::getRole($this->roleId);
    }
    
    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . $this->displayName();
    }

    public function gravatarHash() : ?string
    {
        if (strlen($this->email) == 0) {
            return null;
        }

        $email = trim($this->email);
        $email = strtolower($email);

        $hash = md5($email);

        return $hash;
    }

    public function gravatarUrl() : string
    {
        return self::$linker->gravatarUrl(
            $this->gravatarHash()
        );
    }
}
