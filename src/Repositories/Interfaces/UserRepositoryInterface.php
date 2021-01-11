<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface UserRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id): ?User;
    function create(array $data): User;
    function save(User $user): User;
    function getByLogin(string $login): ?User;
}
