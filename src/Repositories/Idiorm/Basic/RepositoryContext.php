<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\Db;

class RepositoryContext
{
    private Access $access;
    private AuthInterface $auth;
    private CacheInterface $cache;
    private Db $db;

    public function __construct(
        Access $access,
        AuthInterface $auth,
        CacheInterface $cache,
        Db $db
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->cache = $cache;
        $this->db = $db;
    }

    public function access() : Access
    {
        return $this->access;
    }

    public function auth() : AuthInterface
    {
        return $this->auth;
    }

    public function cache() : CacheInterface
    {
        return $this->cache;
    }

    public function db() : Db
    {
        return $this->db;
    }
}
