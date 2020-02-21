<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;

class DoubleBracketsConfig extends LinkMapperSource
{
    public function __construct()
    {
        $this->setDefaultMapper(new PageLinkMapper());
        $this->register('news', new NewsLinkMapper());
        $this->register('tag', new TagLinkMapper());
    }
}
