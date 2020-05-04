<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Models\Interfaces\DbModelInterface;

interface ProtectedRepositoryInterface
{
    public function getProtected(?int $id) : ?DbModelInterface;
}
