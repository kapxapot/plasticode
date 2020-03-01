<?php

namespace Plasticode\Parsing\LinkMappers\Traits;

use Plasticode\Parsing\SlugChunk;

/**
 * Link mapper tag support.
 */
trait Tagged
{
    public abstract function tag() : string;

    /**
     * Maps tagged slug and other chunks to a link.
     *
     * @param SlugChunk $slugChunk
     * @param string[] $otherChunks
     * @return string|null
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $this->validateSlugChunk($slugChunk);

        $rawSlug = $slugChunk->slug();

        $slug = $this->escapeSlug($rawSlug);
        $text = $otherChunks[0] ?? $rawSlug;

        return $this->renderSlug($slug, $text);
    }

    protected function validateSlugChunk(SlugChunk $slugChunk) : bool
    {
        return $slugChunk->hasTag() && $this->tag() == $slugChunk->tag();
    }

    protected function escapeSlug(string $slug) : string
    {
        return $slug;
    }

    protected abstract function renderSlug(string $slug, string $text) : ?string;
}
