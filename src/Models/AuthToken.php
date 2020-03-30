<?php

namespace Plasticode\Models;

use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;
use Plasticode\Models\Traits\WithUser;

/**
 * @property string $token
 * @property string|null $expiresAt
 */
class AuthToken extends DbModel
{
    use CreatedAt, UpdatedAt, WithUser;

    public function toString() : string
    {
        return $this->token . ', expires at ' . $this->expiresAt;
    }
}
