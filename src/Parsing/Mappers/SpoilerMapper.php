<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;

class SpoilerMapper implements MapperInterface
{
    public function map(string $content, array $attrs) : array
    {
        return [
            'id' => Numbers::generate(10),
            'title' => Arrays::first($attrs),
            'body' => $content,
        ];
    }
}
