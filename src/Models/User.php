<?php

namespace Plasticode\Models;

use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $email
 * @property integer $roleId
 * @method string gravatarUrl()
 * @method Role role()
 * @method static withGravatarUrl(string|callable $url)
 * @method static withRole(Role|callable $role)
 */
class User extends DbModel
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['gravatarUrl', 'role'];
    }

    public function displayName() : string
    {
        return $this->name ?? $this->login;
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
}
