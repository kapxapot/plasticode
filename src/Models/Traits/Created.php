<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Models\User;
use Plasticode\Util\Date;

trait Created
{
    public static function filterByCreator(Query $query, User $user) : Query
    {
        return $query->where('created_by', $user->getId());
    }

    public function creator() : ?User
    {
        return self::$container->userRepository->get($this->createdBy);
    }

    public function createdAtIso() : string
    {
        return Date::iso($this->createdAt);
    }
}
