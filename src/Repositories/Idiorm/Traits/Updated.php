<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

trait Updated
{
    protected $updatedAtField = 'updated_at';
    protected $updatedByField = 'updated_by';

    public static function filterByUpdaterQuery(Query $query, User $user) : Query
    {
        return $query
            ->where($this->updatedByField, $user->getId());
    }
}
