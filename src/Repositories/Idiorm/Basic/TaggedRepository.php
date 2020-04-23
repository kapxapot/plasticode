<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Collection;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\TagLink;
use Plasticode\Util\Strings;

abstract class TaggedRepository extends IdiormRepository
{
    protected TagRepositoryInterface $tagRepository;
    protected LinkerInterface $linker;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        TagRepositoryInterface $tagRepository,
        LinkerInterface $linker,
        $hydrator = null
    )
    {
        parent::__construct($repositoryContext, $hydrator);

        $this->tagRepository = $tagRepository;
        $this->linker = $linker;
    }

    /**
     * Override this if entity type is different from table name.
     */
    protected function getTagsEntityType() : string
    {
        return $this->getTable();
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

    protected function withTagLinks(TaggedInterface $entity) : TaggedInterface
    {
        $tab = $this->getTagsEntityType();
        $tags = $entity->getTags();

        $tagLinks = array_map(
            fn (string $t) => new TagLink($t, $this->linker->tag($t, $tab)),
            $tags
        );

        return $entity->withTagLinks(
            TagLinkCollection::make($tagLinks)
        );
    }
}
