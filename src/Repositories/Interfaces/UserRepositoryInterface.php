<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\User;

interface UserRepositoryInterface
{
    function get(?int $id) : ?User;
    function create(array $data) : User;
    function save(User $user) : User;
    function getByLogin(string $login) : ?User;
}
