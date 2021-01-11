<?php

namespace Plasticode\Repositories\Idiorm\Core;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\DbMetadata;

class RepositoryContext
{
    private Access $access;
    private AuthInterface $auth;
    private CacheInterface $cache;
    private DbMetadata $dbMetadata;

    public function __construct(
        Access $access,
        AuthInterface $auth,
        CacheInterface $cache,
        DbMetadata $dbMetadata
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->cache = $cache;
        $this->dbMetadata = $dbMetadata;
    }

    public function access(): Access
    {
        return $this->access;
    }

    public function auth(): AuthInterface
    {
        return $this->auth;
    }

    public function cache(): CacheInterface
    {
        return $this->cache;
    }

    public function dbMetadata(): DbMetadata
    {
        return $this->dbMetadata;
    }
}
