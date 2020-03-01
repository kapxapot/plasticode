<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Parsing\LinkMappers\Basic\TaggedLinkMapper;
use Plasticode\Parsing\LinkMappers\Traits\SimpleMapSlug;
use Plasticode\Parsing\SlugChunk;

class TaggedLinkMapperMock extends TaggedLinkMapper
{
    use SimpleMapSlug;

    public function tag() : string
    {
        return 'mock';
    }

    protected function renderSlug(string $slug, string $text) : ?string
    {
        return $this->tag() . ': <' . $slug . '>' . $text . '</' . $slug . '>';
    }
}
