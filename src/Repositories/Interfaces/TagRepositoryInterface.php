<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Collections\TagCollection;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\Basic\SearchableRepositoryInterface;

interface TagRepositoryInterface extends SearchableRepositoryInterface
{
    function store(array $data) : Tag;
    function getIdsByTag(string $entityType, string $tag) : ScalarCollection;
    function getAllByTag(string $tag) : TagCollection;
    function exists(string $tag) : bool;
    function deleteByEntity(string $entityType, int $entityId) : bool;
}
