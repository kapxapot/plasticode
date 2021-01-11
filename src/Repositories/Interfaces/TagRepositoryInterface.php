<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\Generic\ScalarCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\Generic\SearchableRepositoryInterface;

interface TagRepositoryInterface extends SearchableRepositoryInterface
{
    function store(array $data): Tag;
    function getIdsByTag(string $entityType, string $tag): ScalarCollection;
    function getAllByTag(string $tag): TagCollection;
    function exists(string $tag): bool;
    function deleteByEntity(string $entityType, int $entityId): bool;
}
