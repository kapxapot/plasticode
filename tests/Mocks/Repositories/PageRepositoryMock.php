<?php

namespace Plasticode\Tests\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Page;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock implements PageRepositoryInterface
{
    /** @var Collection */
    private $pages;

    public function __construct(ArraySeederInterface $pageSeeder)
    {
        $this->pages = Collection::make($pageSeeder->seed());
    }

    public function getBySlug(string $slug): ?Page
    {
        return $this->pages
            ->where('slug', $slug)
            ->first();
    }
}
