<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;

class AuthTokenRepository extends IdiormRepository implements AuthTokenRepositoryInterface
{
    protected function entityClass(): string
    {
        return AuthToken::class;
    }

    public function get(?int $id): ?AuthToken
    {
        return $this->getEntity($id);
    }

    public function save(AuthToken $authToken): AuthToken
    {
        return $this->saveEntity($authToken);
    }

    public function store(array $data): AuthToken
    {
        return $this->storeEntity($data);
    }

    public function getByToken(?string $token): ?AuthToken
    {
        return $this
            ->query()
            ->where('token', $token)
            ->one();
    }
}
