<?php

namespace Plasticode\Models;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string|null $expiresAt
 * @property integer $id
 * @property string $token
 * @property integer $userId
 * @method User user()
 * @method static withUser(User|callable $user)
*/
class AuthToken extends DbModel
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['user'];
    }

    public function isExpired() : bool
    {
        return $this->expiresAt && strtotime($this->expiresAt) < time();
    }

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
