<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\Basic\TaggedCollection;

interface TaggedRepositoryInterface extends RepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : TaggedCollection;
}
