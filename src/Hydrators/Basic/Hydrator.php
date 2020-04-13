<?php

namespace Plasticode\Hydrators\Basic;

use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\Traits\Frozen;

abstract class Hydrator implements HydratorInterface
{
    use Frozen;

    abstract public function hydrate(DbModel $entity) : DbModel;
}
