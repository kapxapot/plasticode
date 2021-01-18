<?php

namespace Plasticode\Testing\Mocks\LinkMappers;

use Plasticode\Parsing\LinkMappers\Generic\SlugLinkMapper;
use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

class GenericLinkMapperMock extends SlugLinkMapper
{
    protected function validateSlugChunk(SlugChunk $slugChunk): void
    {
        Assert::notNull($slugChunk->tag());
    }

    /**
     * Maps slug and chunks to a generic link.
     *
     * @param string[] $otherChunks
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks): ?string
    {
        $tag = $slugChunk->tag();

        $slug = $slugChunk->slug();
        $text = $otherChunks[0] ?? $slug;

        return '<a href="http://generic/' . $tag . '/' . $slug . '">' . $text . '</a>';
    }
}
