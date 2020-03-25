<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Data\Db;
use Plasticode\Models\AuthToken;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class AuthTokenRepository extends IdiormRepository implements AuthTokenRepositoryInterface
{
    protected string $entityClass = AuthToken::class;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        Db $db,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct($db);

        $this->userRepository = $userRepository;
    }

    /**
     * @param AuthToken $entity
     */
    protected function hydrate(DbModel $entity) : AuthToken
    {
        return $entity
            ->withUser(
                $this->userRepository->get($entity->userId())
            );
    }

    public function get(?int $id) : ?AuthToken
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
