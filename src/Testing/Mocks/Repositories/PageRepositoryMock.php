<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Testing\Dummies\PageCollectionDummy;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock implements PageRepositoryInterface
{
    private PageCollectionDummy $pages;

    public function __construct(ArraySeederInterface $pageSeeder)
    {
        $this->pages = PageCollectionDummy::make($pageSeeder->seed());
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
