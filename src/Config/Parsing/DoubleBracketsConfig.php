<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Parsing\LinkMappers\NewsMapper;
use Plasticode\Parsing\LinkMappers\PageMapper;
use Plasticode\Parsing\LinkMapperSource;

class DoubleBracketsConfig extends LinkMapperSource
{
    public function __construct()
    {
        $this->setDefaultMapper(new PageMapper());
        $this->register('news', new NewsMapper());
    }
}
