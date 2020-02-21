<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Core\Interfaces\LinkerInterface;

final class LinkerMock implements LinkerInterface
{
    public function abs(string $url = null) : string
    {
        return 'http://abs' . $url;
    }

    public function page(string $slug = null) : string
    {
        return '/' . $slug;
    }

    public function news(int $id = null) : string
    {
        return '/news/' . $id;
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
