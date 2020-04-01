<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\Basic\TaggedEntityLinkMapper;
use Plasticode\Util\Strings;

/**
 * Tag link format: [[tag:id|text]]
 */
class TagLinkMapper extends TaggedEntityLinkMapper
{
    protected function entity() : string
    {
        return 'tag';
    }

    protected function baseUrl() : string
    {
        return $this->linker->tag();
    }

    protected function escapeSlug(string $slug) : string
    {
        return Strings::fromSpaces($slug, '+');
    }
}
