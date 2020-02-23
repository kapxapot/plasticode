<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

abstract class TaggedEntityLinkMapper extends EntityLinkMapper
{
    /**
     * Maps tagged slug and other chunks to a link.
     *
     * @param SlugChunk $slugChunk
     * @param string[] $otherChunks
     * @return string|null
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        Assert::eq($this->entity, $slugChunk->tag());

        $rawSlug = $slugChunk->slug();

        $slug = $this->escapeSlug($rawSlug);
        $text = $otherChunks[0] ?? $rawSlug;

        return $this->renderPlaceholder($slug, $text);
    }

    protected function escapeSlug(string $slug) : string
    {
        return $slug;
    }
}
