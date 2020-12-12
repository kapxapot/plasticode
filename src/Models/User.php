<?php

namespace Plasticode\Models;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string $email
 * @property integer $id
 * @property string $login
 * @property string $name
 * @property string $password
 * @property integer $roleId
 * @method string|null gravatarUrl()
 * @method Role role()
 * @method static withGravatarUrl(string|callable|null $url)
 * @method static withRole(Role|callable $role)
 */
class User extends DbModel implements CreatedAtInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths() : array
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
}
