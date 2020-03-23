<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Page;

interface PageRepositoryInterface
{
    function getBySlug(?string $slug) : ?Page;
}
