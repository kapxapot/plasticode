<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Auth\Auth;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublish;

class ProtectedRepository extends IdiormRepository
{
    use FullPublish;

    /** @var Auth */
    private $auth;

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

    /**
     * Gets protected entity.
     *
     * @param string|integer $id
     * @return DbModel|null
     */
    protected function getProtectedEntity($id) : ?DbModel
    {
        return $this
            ->protectedQuery()
            ->filterById($id)
            ->one();
    }
}
