<?php

namespace Plasticode\Models;

use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;
use Webmozart\Assert\Assert;

/**
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $email
 * @property integer $roleId
 */
class User extends DbModel
{
    use CreatedAt, UpdatedAt;

    protected ?Role $role = null;
    protected ?string $gravatarUrl = null;

    private bool $roleInitialized = false;
    private bool $gravatarUrlInitialized = false;

    public function withRole(Role $role) : self
    {
        $this->role = $role;
        $this->roleInitialized = true;

        return $this;
    }

    public function withGravatarUrl(string $url) : self
    {
        $this->gravatarUrl = $url;
        $this->gravatarUrlInitialized = true;

        return $this;
    }

    public function displayName() : string
    {
        return $this->name ?? $this->login;
    }

    public function role() : ?Role
    {
        Assert::true($this->roleInitialized);

        return $this->role;
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

    public function gravatarUrl() : ?string
    {
        Assert::true($this->gravatarUrlInitialized);

        return $this->gravatarUrl;
    }
}
