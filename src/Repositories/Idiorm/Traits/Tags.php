<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;

trait Tags
{
    protected function getByTagQuery(
        TagRepositoryInterface $tagRepository,
        Query $baseQuery,
        string $tag
    ) : Query
    {
        $tag = Strings::normalize($tag);
        $ids = $tagRepository->getIdsByTag($this->getTable(), $tag);

        if ($ids->empty()) {
            return Query::empty();
        }
        
        $query = $baseQuery->whereIn('id', $ids);

        if (method_exists(static::class, 'tagsWhere')) {
            $query = $this->tagsWhere($query);
        }
        
        return $query;
    }

    public abstract function getTable() : string;
}
