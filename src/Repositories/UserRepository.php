<?php

namespace Plasticode\Repositories;

use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(?array $data = null) : User
    {
        return User::create($data);
    }

    public function save(User $user) : User
    {
        return User::save($user);
    }

    public function getByLogin(string $login) : ?User
    {
        return User::query()
            ->whereAnyIs(
                [
                    ['login' => $login],
                    ['email' => $login],
                ]
            )
            ->one();
    }

    public function getRules(array $data) : array
    {
        return User::getRules($data);
    }
}
