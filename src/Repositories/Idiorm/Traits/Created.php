<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

trait Created
{
    protected static $createdByField = 'created_by';

    public function filterByCreatorQuery(Query $query, User $user) : Query
    {
        return $query
            ->where(static::$createdByField, $user->getId());
    }
}
