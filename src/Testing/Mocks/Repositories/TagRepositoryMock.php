<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Search\SearchParams;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TagRepositoryMock extends RepositoryMock implements TagRepositoryInterface
{
    private TagCollection $tags;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->tags = TagCollection::make($seeder->seed());
    }

    public function store(array $data): Tag
    {
        $tag = new Tag($data);

        if (!$tag->isPersisted()) {
            $tag->id = $this->tags->nextId();
        }

        $this->tags = $this->tags->add($tag);

        return $tag;
    }

    public function getIdsByTag(string $entityType, string $tag): NumericCollection
    {
        return $this
            ->getAllByTag($tag)
            ->where('entity_type', $entityType)
            ->numerize('entity_id');
    }

    public function getAllByTag(string $tag): TagCollection
    {
        return $this
            ->tags
            ->where('tag', $tag);
    }

    public function exists(string $tag): bool
    {
        return $this->getAllByTag($tag)->any();
    }

    public function deleteByEntity(string $entityType, int $entityId): bool
    {
        $this->tags = $this->tags
            ->where(
                function (Tag $tag) use ($entityType, $entityId) {
                    return !($tag->entityId == $entityId && $tag->entityType == $entityType);
                }
            );

        return true;
    }

    public function search(SearchParams $searchParams): TagCollection
    {
        // placeholder
        return $this->getAllByTag($searchParams->filter());
    }
}
