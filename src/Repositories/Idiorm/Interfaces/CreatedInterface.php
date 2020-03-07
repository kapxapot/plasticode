<?php

namespace Plasticode\Repositories\Interfaces\Traits;

use Plasticode\Query;
use Plasticode\Models\User;

interface CreatedInterface
{
    public function filterByCreatorQuery(Query $query, User $user) : Query;
}
