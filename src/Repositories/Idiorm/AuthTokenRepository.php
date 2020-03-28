<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;

class AuthTokenRepository extends IdiormRepository implements AuthTokenRepositoryInterface
{
    protected string $entityClass = AuthToken::class;

    public function get(?int $id) : ?AuthToken
    {
        return $this->getEntity($id);
    }

    public function save(AuthToken $authToken) : AuthToken
    {
        return $this->saveEntity($authToken);
    }

    public function getByToken(?string $token) : ?AuthToken
    {
        return $this
            ->query()
            ->where('token', $token)
            ->one();
    }
}
