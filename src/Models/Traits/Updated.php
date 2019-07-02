<?php

namespace Plasticode\Models\Traits;

use Plasticode\Query;
use Plasticode\Models\User;
use Plasticode\Util\Date;

trait Updated
{
    public static function filterByUpdater(Query $query, User $user) : Query
    {
        return $query->where('updated_by', $user->getId());
    }

    public function updater()
    {
        return static::getUser($this->updatedBy);
    }

    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
}
