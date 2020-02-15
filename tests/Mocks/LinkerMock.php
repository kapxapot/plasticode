<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Core\Interfaces\LinkerInterface;

final class LinkerMock implements LinkerInterface
{
    public function abs(string $url = null) : string
    {
        return $url;
    }

    public function tag(string $tag = null, string $tab = null) : string
    {
        return $tag;
    }

    public function youtube(string $code) : string
    {
        return 'https://youtube.com/watch?v=' . $code;
    }
}
