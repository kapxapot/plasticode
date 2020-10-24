<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\UserCollection;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class UserRepositoryMock implements UserRepositoryInterface
{
    private UserCollection $users;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->users = UserCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?User
    {
        return $this->users->first('id', $id);
    }

    public function create(array $data) : User
    {
        return User::create($data);
    }

    public function save(User $user) : User
    {
        // no need to save anything if the entity is already in the repo
        if ($this->users->contains($user)) {
            return $user;
        }

        // if the entity doesn't have an id, it must be generated
        if (!$user->isPersisted()) {
            $user->id = $this->users->nextId();
        }

        $this->users = $this->users->add($user);

        return $user;
    }

    public function getByLogin(string $login) : ?User
    {
        return $this->users->first('login', $login);
    }
}
