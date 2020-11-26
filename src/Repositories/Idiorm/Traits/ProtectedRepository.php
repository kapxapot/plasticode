<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Models\Basic\DbModel;
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
            ->apply(
                fn (Query $q) => $this->filterById($q, $id)
            )
            ->one();
    }

    protected function protectedQuery() : Query
    {
        return $this->protectQuery(
            $this->query(),
            $this->auth()->getUser()
        );
    }
}
