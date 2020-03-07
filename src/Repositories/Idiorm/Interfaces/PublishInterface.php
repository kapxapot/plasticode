<?php

namespace Plasticode\Repositories\Idiorm\Interfaces;

use Plasticode\Query;

interface PublishInterface
{
    public function getPublishedQuery(Query $query) : Query;
}
