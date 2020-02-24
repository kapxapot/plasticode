<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;

interface MenuRepositoryInterface
{
    public function getAll() : Collection;
}
