<?php

namespace Plasticode\Parsing\LinkMappers\Basic;

use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\SlugChunk;
use Plasticode\Util\Arrays;
use Webmozart\Assert\Assert;

abstract class SlugLinkMapper implements LinkMapperInterface
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

        return $this->mapSlug(
            $slugChunk,
            Arrays::skip($chunks, 1)
        );
    }

    public abstract function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string;

    /**
     * Parses chunk in the form of "tag:slug" or just "slug" as a slug chunk.
     *
     * @param string $chunk
     * @return SlugChunk
     */
    public static function toSlugChunk(string $chunk) : SlugChunk
    {
        $parts = preg_split('/:/', $chunk, null, PREG_SPLIT_NO_EMPTY);

        return count($parts) > 1
            ? new SlugChunk($parts[0], $parts[1])
            : new SlugChunk(null, $parts[0]);
    }
}
