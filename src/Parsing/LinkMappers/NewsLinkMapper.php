<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\Generic\TaggedEntityLinkMapper;

/**
 * News link format: [[news:id|text]]
 */
class NewsLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity(): string
    {
        return 'news';
    }

    protected function baseUrl(): string
    {
        return $this->linker->news();
    }
}
