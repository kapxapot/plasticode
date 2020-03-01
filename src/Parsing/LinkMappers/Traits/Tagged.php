<?php

namespace Plasticode\Parsing\LinkMappers\Traits;

use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

/**
 * Link mapper tag support.
 */
trait Tagged
{
    public abstract function tag() : string;

    protected function validateSlugChunk(SlugChunk $slugChunk) : void
    {
        Assert::true($slugChunk->hasTag());
        Assert::eq($this->tag(), $slugChunk->tag());
    }
}
