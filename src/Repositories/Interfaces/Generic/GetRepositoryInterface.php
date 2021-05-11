<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Models\Interfaces\DbModelInterface;

/**
 * Repository with get($id) method.
 */
interface GetRepositoryInterface extends RepositoryInterface
{
    public function get(?int $id): ?DbModelInterface;
}
