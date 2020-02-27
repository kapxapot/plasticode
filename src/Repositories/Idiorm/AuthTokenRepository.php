<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;

class AuthTokenRepository extends IdiormRepository implements AuthTokenRepositoryInterface
{
    public function get(int $id) : ?AuthToken
    {
        return AuthToken::get($id);
    }

    public function save(AuthToken $authToken) : AuthToken
    {
        return AuthToken::save($authToken);
    }

    public function getByToken(?string $token) : ?AuthToken
    {
        return AuthToken::query()
            ->where('token', $token)
            ->one();
    }
}
