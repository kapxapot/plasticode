<?php

namespace Plasticode\Parsing\LinkMappers;

class NewsLinkMapper extends TaggedEntityLinkMapper
{
    protected $entity = 'news';

    protected function baseUrl() : string
    {
        return $this->linker->news();
    }
}
