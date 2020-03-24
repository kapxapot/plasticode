<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Auth\Auth;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class ProtectedRepository extends IdiormRepository
{
    use FullPublishedRepository;

    private Auth $auth;

    public function __construct(
        Db $db,
        Auth $auth
    )
    {
        parent::__construct($db);

        $this->auth = $auth;
    }

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
