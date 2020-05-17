<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TagRepositoryMock implements TagRepositoryInterface
{
    private TagCollection $tags;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->tags = TagCollection::make($seeder->seed());
    }

    public function store(array $data) : Tag
    {
        $tag = new Tag($data);
        $this->tags = $this->tags->add($tag);

        return $tag;
    }

    public function getIdsByTag(string $entityType, string $tag) : ScalarCollection
    {
        return $this
            ->tags
            ->where('entity_type', $entityType)
            ->where('tag', $tag)
            ->extract('entity_id')
            ->toScalarCollection();
    }

    public function getAllByTag(string $tag) : TagCollection
    {
        return $this
            ->tags
            ->where('tag', $tag);
    }

    public function exists(string $tag) : bool
    {
        return $this->getAllByTag($tag)->any();
    }

    public function deleteByEntity(string $entityType, int $entityId) : bool
    {
        $this->tags = $this->tags
            ->where(
                function (Tag $tag) use ($entityType, $entityId) {
                    return !($tag->entityId == $entityId && $tag->entityType == $entityType);
                }
            );

        return true;
    }

    public function search(string $searchQuery) : TagCollection
    {
        // placeholder
        return $this
            ->tags
            ->where('tag', $searchQuery);
    }
}
