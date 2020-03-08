<?php

namespace Plasticode\Models;

/**
 * @property integer $id
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $email
 * @property integer $roleId
 * @property string $createdAt
 * @property string $updatedAt
 */
class User extends DbModel
{
    public function displayName() : string
    {
        return $this->name ?? $this->login;
    }
    
    public function role() : Role
    {
        return self::$container->roleRepository->get($this->roleId);
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
        return self::$container->linker->gravatarUrl(
            $this->gravatarHash()
        );
    }
}
