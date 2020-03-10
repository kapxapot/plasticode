<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\User;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends IdiormRepository implements UserRepositoryInterface
{
    protected $entityClass = User::class;

    public function get(int $id) : ?User
    {
        return $this->getEntity($id);
    }

    public function create(array $data) : User
    {
        return $this->createEntity($data);
    }

    public function save(User $user) : User
    {
        return $this->saveEntity($user);
    }

    public function getByLogin(string $login) : ?User
    {
        return $this
            ->query()
            ->whereAnyIs(
                [
                    ['login' => $login],
                    ['email' => $login],
                ]
            )
            ->one();
    }
}
