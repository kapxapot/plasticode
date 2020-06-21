<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Interfaces\PageInterface;

interface PageRepositoryInterface
{
    function getBySlug(?string $slug) : ?PageInterface;
}
