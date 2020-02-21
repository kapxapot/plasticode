<?php

namespace Plasticode\Parsing\LinkMappers;

use Webmozart\Assert\Assert;

abstract class TaggedEntityLinkMapper extends EntityLinkMapper
{
    /**
     * Maps chunks to a link.
     *
     * @param string[] $chunks
     * @return string|null
     */
    public function map(array $chunks) : ?string
    {
        Assert::notEmpty($chunks);

        $slugChunk = self::toSlugChunk($chunks[0]);

        Assert::eq(static::$entity, $slugChunk->tag());

        $slug = $slugChunk->slug();

        $escapedSlug = $this->escapeSlug($slug);
        $text = $chunks[1] ?? $slug;

        return $this->renderPlaceholder($escapedSlug, $text);
    }

    protected function escapeSlug(string $slug) : string
    {
        return $slug;
    }
}
