<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Models\Interfaces\DbModelInterface;

interface ProtectedRepositoryInterface extends RepositoryInterface
{
    function getProtected(?int $id): ?DbModelInterface;
}
