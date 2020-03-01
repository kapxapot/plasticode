<?php

namespace Plasticode\Parsing\LinkMappers\Traits;

use Plasticode\Parsing\SlugChunk;

/**
 * Simple map slug scenario with slug + text.
 * [[tag:slug|text]]
 */
trait SimpleMapSlug
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
        $rawSlug = $slugChunk->slug();

        $slug = $this->escapeSlug($rawSlug);
        $text = $otherChunks[0] ?? $rawSlug;

        return $this->renderSlug($slug, $text);
    }

    protected function escapeSlug(string $slug) : string
    {
        return $slug;
    }

    protected abstract function renderSlug(string $slug, string $text) : ?string;
}
