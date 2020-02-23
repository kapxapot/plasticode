<?php

namespace Plasticode\Tests\Mocks;

use Plasticode\Parsing\LinkMappers\SlugLinkMapper;
use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

class GenericLinkMapperMock extends SlugLinkMapper
{
    /**
     * Maps slug and chunks to a generic link.
     *
     * @param SlugChunk $slugChunk
     * @param string[] $otherChunks
     * @return string|null
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $tag = $slugChunk->tag();

        Assert::notNull($tag);

        $slug = $slugChunk->slug();
        $text = $otherChunks[0] ?? $slug;

        return '<a href="http://generic/' . $tag . '/' . $slug . '">' . $text . '</a>';
    }
}
