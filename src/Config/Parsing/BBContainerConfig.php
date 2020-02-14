<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Parsing\Mappers\ListMapper;
use Plasticode\Parsing\Mappers\QuoteMapper;
use Plasticode\Parsing\Mappers\SpoilerMapper;
use Plasticode\Parsing\TagMapperSource;

class BBContainerConfig extends TagMapperSource
{
    public function __construct()
    {
        $this->register('spoiler', new SpoilerMapper());
        $this->register('list', new ListMapper());
        $this->register('quote', new QuoteMapper());
    }
}
