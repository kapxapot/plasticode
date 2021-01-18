<?php

namespace Plasticode\Hydrators\Generic;

use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Traits\Frozen;

abstract class Hydrator implements HydratorInterface
{
    use Frozen;

    abstract public function hydrate(DbModel $entity): DbModel;
}
