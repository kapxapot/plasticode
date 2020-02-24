<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Page;

interface PageRepositoryInterface
{
    public function getBySlug(string $slug) : ?Page;
}
