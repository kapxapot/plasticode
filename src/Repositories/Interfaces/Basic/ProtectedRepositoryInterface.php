<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Models\Interfaces\DbModelInterface;

interface ProtectedRepositoryInterface extends RepositoryInterface
{
    function getProtected(?int $id) : ?DbModelInterface;
}
