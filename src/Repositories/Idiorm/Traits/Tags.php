<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

/**
 * @property TagRepositoryInterface $tagRepository
 */
trait Tags
{
    protected function getByTagQuery(string $tag, Query $query = null) : Query
    {
        Assert::notNull($this->tagRepository);

        $tag = Strings::normalize($tag);
        $ids = $this->tagRepository->getIdsByTag($this->getTable(), $tag);

        if ($ids->empty()) {
            return Query::empty();
        }
        
        $query = $query ?? $this->query();
        $query = $query->whereIn('id', $ids);

        if (method_exists(static::class, 'tagsWhere')) {
            $query = $this->tagsWhere($query);
        }
        
        return $query;
    }

    public abstract function getTable() : string;

    protected abstract function query() : Query;
}
