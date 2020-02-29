<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\Menu;

interface MenuRepositoryInterface
{
    public function get(int $id) : ?Menu;
    public function getAll() : Collection;
}
