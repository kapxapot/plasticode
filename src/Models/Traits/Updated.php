<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Date;

/**
 * @property UserRepositoryInterface $userRepository
 */
trait Updated
{
    public static function filterByUpdater(Query $query, User $user) : Query
    {
        return $query->where('updated_by', $user->getId());
    }

    public function updater() : ?User
    {
        return self::$userRepository->get($this->updatedBy);
    }

    public function updatedAtIso() : string
    {
        return Date::iso($this->updatedAt);
    }
}
