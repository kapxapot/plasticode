<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\Page;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;

class PageRepository extends IdiormRepository implements PageRepositoryInterface
{
    protected $entityClass = Page::class;

    public function getBySlug(string $slug) : ?Page
    {
        return $this
            ->query()
            ->where('slug', $slug)
            ->one();
    }
}
