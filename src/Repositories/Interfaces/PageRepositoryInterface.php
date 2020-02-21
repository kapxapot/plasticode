<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\Page;

interface PageRepositoryInterface extends RepositoryInterface
{
    public function getBySlug(string $slug) : ?Page;
}
