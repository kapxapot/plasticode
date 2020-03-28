<?php

namespace Plasticode\Hydrators;

use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\AuthToken;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class AuthTokenHydrator implements HydratorInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
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
}
