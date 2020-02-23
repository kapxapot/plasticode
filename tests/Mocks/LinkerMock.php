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

    public function page(string $slug = null) : string
    {
        return $this->abs('/') . $slug;
    }

    public function news(int $id = null) : string
    {
        return $this->abs('/news/') . $id;
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        return $this->abs('/tags/') . $tag;
    }

    public function youtube(string $code) : string
    {
        return 'https://youtube.com/watch?v=' . $code;
    }
}
