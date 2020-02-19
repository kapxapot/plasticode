<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Parsing\TagMappers\ListMapper;
use Plasticode\Parsing\TagMappers\QuoteMapper;
use Plasticode\Parsing\TagMappers\SpoilerMapper;
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
