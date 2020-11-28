<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Repositories\Interfaces\Basic\RepositoryInterface;

interface PageRepositoryInterface extends RepositoryInterface
{
    function getBySlug(?string $slug) : ?PageInterface;

    /**
     * Checks duplicates (for validation).
     */
    function lookup(string $slug, int $exceptId = 0) : PageCollection;
}
