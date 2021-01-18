<?php

namespace Plasticode\Hydrators\Interfaces;

use Plasticode\Models\Generic\DbModel;

interface HydratorInterface
{
    function hydrate(DbModel $entity): DbModel;
}
