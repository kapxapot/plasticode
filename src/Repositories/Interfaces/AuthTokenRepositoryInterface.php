<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\AuthToken;

interface AuthTokenRepositoryInterface
{
    public function get(int $id) : ?AuthToken;
    public function save(AuthToken $authToken) : AuthToken;
    public function getByToken(?string $token) : ?AuthToken;
}
