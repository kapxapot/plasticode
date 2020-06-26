<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Interfaces\PageInterface;

interface PageRepositoryInterface
{
    function getBySlug(?string $slug) : ?PageInterface;

    /**
     * Checks duplicates (for validation).
     */
    function lookup(string $slug, int $exceptId = 0) : PageCollection;
}
