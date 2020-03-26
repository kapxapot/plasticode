<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Query;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

/**
 * @property UserRepositoryInterface $userRepository
 */
trait CreatedRepository
{
    protected string $createdAtField = 'created_at';
    protected string $createdByField = 'created_by';

    protected function filterByCreator(Query $query, User $user) : Query
    {
        return $query
            ->where($this->createdByField, $user->getId());
    }

    protected function withCreator(CreatedInterface $entity) : CreatedInterface
    {
        return $entity->withCreator(
            $this->userRepository->get($entity->createdBy())
        );
    }
}
