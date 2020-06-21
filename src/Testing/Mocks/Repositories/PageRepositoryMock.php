<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock implements PageRepositoryInterface
{
    private PageCollection $pages;

    public function __construct(ArraySeederInterface $pageSeeder)
    {
        $this->pages = PageCollection::make($pageSeeder->seed());
    }

    public function getBySlug(?string $slug) : ?PageInterface
    {
        return $this
            ->pages
            ->first(
                fn (PageInterface $p) => $p->getSlug() == $slug
            );
    }
}
