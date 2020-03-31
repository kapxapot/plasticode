<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

trait CreatedRepository
{
    protected string $createdAtField = 'created_at';
    protected string $createdByField = 'created_by';

    protected function filterByCreator(Query $query, User $user) : Query
    {
        return $query
            ->where($this->createdByField, $user->getId());
    }
}
