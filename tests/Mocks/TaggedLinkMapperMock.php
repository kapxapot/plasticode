<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Parsing\LinkMappers\Basic\TaggedLinkMapper;

class TaggedLinkMapperMock extends TaggedLinkMapper
{
    public function tag() : string
    {
        return 'mock';
    }

    protected function renderSlug(string $slug, string $text) : ?string
    {
        return $this->tag() . ': <' . $slug . '>' . $text . '</' . $slug . '>';
    }
}
