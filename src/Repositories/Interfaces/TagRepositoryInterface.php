<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\Tag;

interface TagRepositoryInterface
{
    function getIdsByTag(string $entityType, string $tag) : Collection;
    function getByTag(string $tag) : Collection;
    function exists(string $tag) : bool;
    function search(string $searchQuery) : Collection;

    function store(array $data) : Tag;
    function deleteByEntity(string $entityType, int $entityId) : bool;
}
