<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

trait UpdatedRepository
{
    protected string $updatedAtField = 'updated_at';
    protected string $updatedByField = 'updated_by';

    protected function filterByUpdater(Query $query, User $user) : Query
    {
        return $query
            ->where($this->updatedByField, $user->getId());
    }
}
