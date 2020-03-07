<?php

namespace Plasticode\Repositories\Idiorm\Interfaces;

use Plasticode\Models\User;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\Traits\CreatedInterface;

interface FullPublishInterface extends PublishInterface, CreatedInterface
{
    /**
     * Modifies the query to protect access rights if needed.
     *
     * @param Query $query
     * @param User|null $user
     * @return Query
     */
    public function protectQuery(Query $query, ?User $user) : Query;
}
