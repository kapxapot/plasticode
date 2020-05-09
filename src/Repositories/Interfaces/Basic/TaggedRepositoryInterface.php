<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\Basic\TaggedCollection;

interface TaggedRepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : TaggedCollection;
}
