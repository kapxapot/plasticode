<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Models\Interfaces\DbModelInterface;

/**
 * Repository with get($id) method.
 */
interface GetRepositoryInterface extends RepositoryInterface
{
    function get(?int $id): ?DbModelInterface;
}
