<?php

namespace Plasticode\Hydrators\Interfaces;

use Plasticode\Models\Generic\DbModel;

interface HydratorInterface
{
    public function hydrate(DbModel $entity): DbModel;
}
