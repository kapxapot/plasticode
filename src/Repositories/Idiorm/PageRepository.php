<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\Page;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;

class PageRepository extends TaggedRepository implements PageRepositoryInterface
{
    protected string $entityClass = Page::class;

    public function getBySlug(?string $slug) : ?Page
    {
        return $this
            ->query()
            ->where('slug', $slug)
            ->one();
    }
}
