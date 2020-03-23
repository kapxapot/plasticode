<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\AuthToken;

interface AuthTokenRepositoryInterface
{
    function get(?int $id) : ?AuthToken;
    function save(AuthToken $authToken) : AuthToken;
    function getByToken(?string $token) : ?AuthToken;
}
