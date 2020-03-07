<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\User;

interface UserRepositoryInterface
{
    public function get(int $id) : ?User;
    public function create(array $data) : User;
    public function save(User $user) : User;
    public function getByLogin(string $login) : ?User;
    public function getRules(array $data) : array;
}
