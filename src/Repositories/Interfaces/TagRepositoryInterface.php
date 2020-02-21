<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Tag;

interface TagRepositoryInterface
{
    public function getByTag(string $tag) : ?Tag;
    public function exists(string $tag) : bool;
}
