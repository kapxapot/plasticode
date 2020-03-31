<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Auth\Auth;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

trait ProtectedRepository
{
    use FullPublishedRepository;

    abstract protected function auth() : Auth;

    protected function protectedQuery() : Query
    {
        return $this->protectQuery(
            $this->query(),
            $this->auth->getUser()
        );
    }

    protected function getProtectedEntity(?int $id) : ?DbModel
    {
        return $this
            ->protectedQuery()
            ->filterById($id)
            ->one();
    }
}
