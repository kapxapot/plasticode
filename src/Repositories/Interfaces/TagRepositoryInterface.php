<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collection;
use Plasticode\Query;

interface TagRepositoryInterface
{
    // public function getByEntity(string $entityType, int $entityId) : Query;
    public function getIdsByTag(string $entityType, string $tag) : Collection;
    public function deleteByEntity(string $entityType, int $entityId) : bool;
    public function getByTag(string $tag) : Collection;
    public function exists(string $tag) : bool;
    public function search(string $searchQuery) : Collection;
}
