<?php

namespace Plasticode\Tests\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

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
}
