<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Page;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock implements PageRepositoryInterface
{
    private PageCollection $pages;

    public function __construct(ArraySeederInterface $pageSeeder)
    {
        $this->pages = PageCollection::make($pageSeeder->seed());
    }

    public function getBySlug(?string $slug) : ?Page
    {
        return $this->pages
            ->where('slug', $slug)
            ->first();
    }
}
