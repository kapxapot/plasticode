<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FieldValidatingRepositoryInterface;

interface UserRepositoryInterface extends ChangingRepositoryInterface, FieldValidatingRepositoryInterface
{
    function get(?int $id): ?User;
    function create(array $data): User;
    function save(User $user): User;
    function getByLogin(string $login): ?User;
}
