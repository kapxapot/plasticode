<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Models\Interfaces\ParentedInterface;

interface ParentedRepositoryInterface extends GetRepositoryInterface
{
    function get(?int $id) : ?ParentedInterface;
}
