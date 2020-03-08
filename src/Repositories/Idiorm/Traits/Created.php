<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

trait Created
{
    protected $createdAtField = 'created_at';
    protected $createdByField = 'created_by';

    public function filterByCreatorQuery(Query $query, User $user) : Query
    {
        return $query
            ->where($this->createdByField, $user->getId());
    }
}
