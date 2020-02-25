<?php

namespace Plasticode\Repositories;

use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends IdiormRepository implements UserRepositoryInterface
{
    public function get(int $id) : ?User
    {
        return User::get($id);
    }

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

    /**
     * Todo: supposedly this belongs to a model or some validation settings
     *
     * @param array $data
     * @return array
     */
    public function getRules(array $data) : array
    {
        return User::getRules($data);
    }
}
