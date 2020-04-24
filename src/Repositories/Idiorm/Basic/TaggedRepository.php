<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;

abstract class TaggedRepository extends IdiormRepository
{
    protected TagRepositoryInterface $tagRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        TagRepositoryInterface $tagRepository,
        $hydrator = null
    )
    {
        parent::__construct($repositoryContext, $hydrator);

        $this->tagRepository = $tagRepository;
    }

    protected function byTagQuery(
        Query $query,
        string $tag,
        int $limit = 0
    ) : Query
    {
        $tag = Strings::normalize($tag);

        $ids = $this->tagRepository->getIdsByTag(
            $this->getTable(),
            $tag
        );

        if ($ids->isEmpty()) {
            return Query::empty();
        }

        return $query
            ->whereIn($this->idField(), $ids)
            ->limit($limit);
    }
}
