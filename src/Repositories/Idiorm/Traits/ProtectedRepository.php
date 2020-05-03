<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

trait ProtectedRepository
{
    use FullPublishedRepository;

    abstract protected function query() : Query;
    abstract protected function auth() : AuthInterface;

    protected function getProtectedEntity(?int $id) : ?DbModel
    {
        return $this
            ->protectedQuery()
            ->find($id);
    }

    protected function protectedQuery() : Query
    {
        return $this->protectQuery(
            $this->query(),
            $this->auth()->getUser()
        );
    }
}
