<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Data\Db;
use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class AuthTokenRepository extends IdiormRepository implements AuthTokenRepositoryInterface
{
    protected $entityClass = AuthToken::class;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        Db $db,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct($db);

        $this->userRepository = $userRepository;
    }

    protected function ormObjToEntity(\ORM $ormObj) : AuthToken
    {
        /** @var AuthToken */
        $token = parent::ormObjToEntity($ormObj);

        return $token
            ->withUser(
                $this->userRepository->get($token->userId())
            );
    }

    public function get(int $id) : ?AuthToken
    {
        return $this->getEntity($id);
    }

    public function save(AuthToken $authToken) : AuthToken
    {
        return $this->saveEntity($authToken);
    }

    public function getByToken(?string $token) : ?AuthToken
    {
        return $this
            ->query()
            ->where('token', $token)
            ->one();
    }
}
