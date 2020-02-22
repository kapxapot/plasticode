<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;

interface TagRepositoryInterface extends RepositoryInterface
{
    public function getByTag(string $tag) : Collection;
    public function exists(string $tag) : bool;
}
