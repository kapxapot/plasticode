<?php

namespace Plasticode\Hydrators\Interfaces;

use Plasticode\Models\Basic\DbModel;

interface HydratorInterface
{
    function hydrate(DbModel $entity) : DbModel;
}
