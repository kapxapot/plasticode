<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Data\Db;

class RepositoryContext
{
    private Access $access;
    private Auth $auth;
    private Db $db;

    public function __construct(Access $access, Auth $auth, Db $db)
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->db = $db;
    }

    public function access() : Access
    {
        return $this->access;
    }

    public function auth() : Auth
    {
        return $this->auth;
    }

    public function db() : Db
    {
        return $this->db;
    }
}
