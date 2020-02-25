<?php

namespace Plasticode\Repositories;

use Plasticode\Models\Page;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;

class PageRepository extends IdiormRepository implements PageRepositoryInterface
{
    public function getBySlug(string $slug) : ?Page
    {
        return Page::query()
            ->where('slug', $slug)
            ->one();
    }
}
