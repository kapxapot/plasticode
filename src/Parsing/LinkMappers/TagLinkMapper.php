<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Util\Strings;

class TagLinkMapper extends TaggedEntityLinkMapper
{
    protected $entity = 'tag';

    protected function baseUrl() : string
    {
        return $this->linker->tag();
    }

    protected function escapeSlug(string $slug): string
    {
        return Strings::fromSpaces($slug, '+');
    }
}
