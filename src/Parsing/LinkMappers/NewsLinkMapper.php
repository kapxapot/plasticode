<?php

namespace Plasticode\Parsing\LinkMappers;

class NewsLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity() : string
    {
        return 'news';
    }

    protected function baseUrl() : string
    {
        return $this->linker->news();
    }
}
