<?php

namespace Plasticode\Tests\Mocks\Repositories;

use Plasticode\Collection;
use Plasticode\Models\Page;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Util\Date;

class PageRepositoryMock implements PageRepositoryInterface
{
    /** @var Collection */
    private $pages;

    public function __construct()
    {
        $this->pages = Collection::make(
            [
                new Page(
                    [
                        'id' => 1,
                        'slug' => 'about-us',
                        'title' => 'About us',
                        'text' => 'We are awesome. Work with us.',
                        'published' => 1,
                        'published_at' => Date::dbNow(),
                    ]
                ),
                new Page(
                    [
                        'id' => 2,
                        'slug' => 'illidan-stormrage',
                        'title' => 'Illidan Stormrage',
                        'text' => 'Illidan is a bad boy. Once a night elf, now a demon. Booo.',
                        'published' => 0,
                        'published_at' => null,
                    ]
                ),
            ]
        );
    }

    public function getBySlug(string $slug): ?Page
    {
        return $this->pages
            ->where('slug', $slug)
            ->first();
    }
}
