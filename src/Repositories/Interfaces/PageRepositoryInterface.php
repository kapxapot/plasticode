<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;

interface PageRepositoryInterface extends RepositoryInterface
{
    function getBySlug(?string $slug): ?PageInterface;

    /**
     * Checks for duplicates (for validation).
     */
    function lookup(string $slug, int $exceptId = 0): PageCollection;
}
