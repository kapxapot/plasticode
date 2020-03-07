<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TagRepositoryMock implements TagRepositoryInterface
{
    /** @var Collection */
    private $tags;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->tags = Collection::make($seeder->seed());
    }

    public function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return $this->tags
            ->where('entity_type', $entityType)
            ->where('tag', $tag)
            ->extract('entity_id');
    }

    public function getByTag(string $tag) : Collection
    {
        return $this->tags
            ->where('tag', $tag);
    }

    public function exists(string $tag) : bool
    {
        return $this->getByTag($tag)->any();
    }

    public function search(string $searchQuery) : Collection
    {
        return $this->tags
            ->where('tag', $searchQuery);
    }

    public function store(array $data) : Tag
    {
        $tag = new Tag($data);
        $this->tags = $this->tags->add($tag);

        return $tag;
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
}
