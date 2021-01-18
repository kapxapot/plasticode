<?php

namespace Plasticode\Testing\Mocks\LinkMappers;

use Plasticode\Parsing\LinkMappers\Generic\TaggedLinkMapper;
use Plasticode\Parsing\LinkMappers\Traits\SimpleMapSlug;

class TaggedLinkMapperMock extends TaggedLinkMapper
{
    use SimpleMapSlug;

    public function tag(): string
    {
        return 'mock';
    }

    protected function renderSlug(string $slug, string $text): ?string
    {
        return $this->tag() . ': <' . $slug . '>' . $text . '</' . $slug . '>';
    }
}
