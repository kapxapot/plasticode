<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Models\Interfaces\UpdatedInterface;
use Plasticode\Query;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

/**
 * @property UserRepositoryInterface $userRepository
 */
trait UpdatedRepository
{
    protected $updatedAtField = 'updated_at';
    protected $updatedByField = 'updated_by';

    public static function filterByUpdater(Query $query, User $user) : Query
    {
        return $query
            ->where($this->updatedByField, $user->getId());
    }

    protected function withUpdater(UpdatedInterface $entity) : UpdatedInterface
    {
        return $entity->withUpdater(
            $this->userRepository->get($entity->updatedBy())
        );
    }
}
