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

    public function creator()
    {
        return static::getUser($this->createdBy);
    }

    public function createdAtIso()
    {
        return Date::iso($this->createdAt);
    }
}
