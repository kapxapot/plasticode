<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Collections\Basic\TaggedCollection;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Interfaces\Basic\TaggedRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;

abstract class TaggedRepository extends IdiormRepository implements TaggedRepositoryInterface
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

    abstract public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : TaggedCollection;

    protected function filterByTag(
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

        return $query
            ->whereIn($this->idField(), $ids)
            ->limit($limit);
    }
}
