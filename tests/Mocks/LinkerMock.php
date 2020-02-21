<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\News;
use Plasticode\Models\Page;

final class LinkerMock implements LinkerInterface
{
    public function abs(string $url = null) : string
    {
        return 'http://abs' . $url;
    }

    public function page(?Page $page = null) : string
    {
        return '/' . ($page ? $page->slug : null);
    }

    public function news(?News $news = null) : string
    {
        return '/news/' . ($news ? $news->getId() : null);
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        return '/tags/' . $tag;
    }

    public function youtube(string $code) : string
    {
        return 'https://youtube.com/watch?v=' . $code;
    }
}
