<?php

namespace Plasticode\Hydrators\Interfaces;

use Plasticode\Models\DbModel;

interface HydratorInterface
{
    function hydrate(DbModel $entity) : DbModel;
}
