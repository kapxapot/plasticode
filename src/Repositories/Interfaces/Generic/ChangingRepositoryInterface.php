<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Models\Interfaces\UpdatedAtInterface;

/**
 * Repository with get($id) method returning a model with $updatedAt property.
 */
interface ChangingRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?UpdatedAtInterface;
}
