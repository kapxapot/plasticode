<?php

namespace Plasticode\Hydrators;

use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\AuthToken;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class AuthTokenHydrator extends Hydrator
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param AuthToken $entity
     */
    public function hydrate(DbModel $entity): AuthToken
    {
        return $entity
            ->withUser(
                fn () => $this->userRepository->get($entity->userId)
            );
    }
}
