<?php

namespace Plasticode\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository implements TagRepositoryInterface
{
    public function getByTag(string $tag) : Collection
    {
        return $this->byTagQuery($tag)->all();
    }

    public function exists(string $tag) : bool
    {
        return $this->byTagQuery($tag)->any();
    }

    private function byTagQuery(string $tag) : Query
    {
        return Tag::query()
            ->where('tag', $tag);
    }
}
