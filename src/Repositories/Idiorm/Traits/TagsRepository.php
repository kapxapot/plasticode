<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Collections\TagLinkCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Interfaces\TagsInterface;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\TagLink;
use Plasticode\Util\Strings;

/**
 * @property LinkerInterface $linker
 */
trait TagsRepository
{
    /**
     * Override this if entity type is different from table name.
     */
    protected function getTagsEntityType() : string
    {
        return $this->getTable();
    }

    protected function getByTagQuery(
        TagRepositoryInterface $tagRepository,
        Query $baseQuery,
        string $tag
    ) : Query
    {
        $tag = Strings::normalize($tag);
        $ids = $tagRepository->getIdsByTag($this->getTable(), $tag);

        if ($ids->isEmpty()) {
            return Query::empty();
        }

        $query = $baseQuery->whereIn('id', $ids);

        if (method_exists(static::class, 'tagsWhereQuery')) {
            $query = $this->tagsWhereQuery($query);
        }

        return $query;
    }

    public abstract function getTable() : string;

    protected function withTagLinks(TagsInterface $entity) : TagsInterface
    {
        $tab = $this->getTagsEntityType();
        $tags = $entity->getTags();

        $tagLinks = array_map(
            fn ($t) => new TagLink($t, $this->linker->tag($t, $tab)),
            $tags
        );

        return $entity->withTagLinks(
            TagLinkCollection::make($tagLinks)
        );
    }
}
