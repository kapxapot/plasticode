<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\TaggedCollection;

interface TaggedRepositoryInterface extends RepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0): TaggedCollection;
}
