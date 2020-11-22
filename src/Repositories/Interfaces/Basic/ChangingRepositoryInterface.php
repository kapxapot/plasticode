<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Models\Interfaces\UpdatedAtInterface;

/**
 * Repository with get($id) method returning a model with $updatedAt property.
 */
interface ChangingRepositoryInterface extends GetRepositoryInterface
{
    function get(?int $id) : ?UpdatedAtInterface;
}
