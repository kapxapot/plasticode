<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Interfaces\Basic\ChangingRepositoryInterface;

interface AuthTokenRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id) : ?AuthToken;
    function save(AuthToken $authToken) : AuthToken;
    function store(array $data) : AuthToken;
    function getByToken(?string $token) : ?AuthToken;
}
