<?php

namespace Plasticode\Testing\Mocks\Repositories;

use Plasticode\Collections\PageCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class PageRepositoryMock extends RepositoryMock implements PageRepositoryInterface
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

    /**
     * Checks duplicates (for validation).
     */
    public function lookup(string $slug, int $exceptId = 0) : PageCollection
    {
        return $this
            ->pages
            ->where(
                fn (PageInterface $p) => $p->getSlug() == $slug && $p->getId() !== $exceptId
            );
    }
}
