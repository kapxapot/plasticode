<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Page;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock implements PageRepositoryInterface
{
    private Collection $pages;

    public function __construct(ArraySeederInterface $pageSeeder)
    {
        $this->pages = Collection::make($pageSeeder->seed());
    }

    public function getBySlug(?string $slug): ?Page
    {
        return $this->pages
            ->where('slug', $slug)
            ->first();
    }
}
