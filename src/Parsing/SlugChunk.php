<?php

namespace Plasticode\Parsing;

class SlugChunk
{
    private ?string $tag;
    private string $slug;

    public function __construct(?string $tag, string $slug)
    {
        $this->tag = $tag;
        $this->slug = $slug;
    }

    public function tag() : ?string
    {
        return $this->tag;
    }

    public function slug() : string
    {
        return $this->slug;
    }

    public function hasTag() : bool
    {
        return strlen($this->tag) > 0;
    }
}
