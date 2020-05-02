<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\MenuCollection;
use Plasticode\Models\Menu;

interface MenuRepositoryInterface
{
    function get(?int $id) : ?Menu;
    function getAll() : MenuCollection;
}
