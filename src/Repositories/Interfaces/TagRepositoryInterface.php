<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;

interface TagRepositoryInterface
{
    public function getByTag(string $tag) : Collection;
    public function exists(string $tag) : bool;
}
