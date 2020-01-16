<?php

namespace Plasticode\Config;

use Plasticode\Config\Interfaces\BBContainerConfigInterface;
use Plasticode\Parsing\Mappers\ListMapper;
use Plasticode\Parsing\Mappers\QuoteMapper;
use Plasticode\Parsing\Mappers\SpoilerMapper;

class BBContainerConfig implements BBContainerConfigInterface
{
    public function getMappers() : array
    {
        return [
            'spoiler' => new SpoilerMapper(),
            'list' => new ListMapper(),
            'quote' => new QuoteMapper(),
        ];
    }
}
