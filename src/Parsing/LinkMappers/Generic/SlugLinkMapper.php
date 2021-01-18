<?php

namespace Plasticode\Parsing\LinkMappers\Generic;

use InvalidArgumentException;
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
     */
    public function map(array $chunks): ?string
    {
        Assert::notEmpty($chunks);
    
        $slugChunk = self::toSlugChunk($chunks[0]);

        $this->validateSlugChunk($slugChunk);

        return $this->mapSlug(
            $slugChunk,
            Arrays::skip($chunks, 1)
        );
    }

    /**
     * This method must throw {@see InvalidArgumentException} (use Assert) if the slug chunk is not valid.
     * 
     * @throws InvalidArgumentException
     */
    protected function validateSlugChunk(SlugChunk $slugChunk): void
    {
    }

    abstract protected function mapSlug(SlugChunk $slugChunk, array $otherChunks): ?string;

    /**
     * Parses chunk in the form of "tag:slug" or just "slug" as a slug chunk.
     */
    public static function toSlugChunk(string $chunk): SlugChunk
    {
        $parts = preg_split('/:/', $chunk, null, PREG_SPLIT_NO_EMPTY);

        return count($parts) > 1
            ? new SlugChunk($parts[0], $parts[1])
            : new SlugChunk(null, $parts[0]);
    }
}
