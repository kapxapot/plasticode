<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\Menu;

interface MenuRepositoryInterface
{
    function get(?int $id) : ?Menu;
    function getAll() : Collection;
}
