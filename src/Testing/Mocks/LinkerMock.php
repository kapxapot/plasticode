<?php

namespace Plasticode\Testing\Mocks;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\IO\Image;

class LinkerMock implements LinkerInterface
{
    public function abs(string $url = null) : string
    {
        return 'http://abs' . $url;
    }

    public function rel(string $url = null) : string
    {
        return '/' . rtrim($url, '/');
    }

    public function getExtension(?string $type) : ?string
    {
        return Image::getExtension($type ?? 'jpeg');
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

    public function twitch(string $id) : string
    {
        return 'https://twitch.tv/' . $id;
    }

    public function youtube(string $code) : string
    {
        return 'https://youtube.com/watch?v=' . $code;
    }
}
