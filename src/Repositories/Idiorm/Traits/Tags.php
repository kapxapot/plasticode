<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

/**
 * To use this trait you MUST declare:
 * 
 * - private TagRepositoryInterface $tagRepository
 * - public function getTable() : string (already declared in IdiormRepository class)
 * 
 * @property TagRepositoryInterface $tagRepository
 */
trait Tags
{
    protected function getByTagQuery(string $tag, Query $baseQuery) : Query
    {
        Assert::notNull($this->tagRepository);

        $tag = Strings::normalize($tag);
        $ids = $this->tagRepository->getIdsByTag($this->getTable(), $tag);

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
