<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Models\Tag;

interface TagRepositoryInterface
{
    function store(array $data) : Tag;
    function getIdsByTag(string $entityType, string $tag) : Collection;
    function getAllByTag(string $tag) : Collection;
    function exists(string $tag) : bool;
    function deleteByEntity(string $entityType, int $entityId) : bool;
    function search(string $searchQuery) : Collection;
}
