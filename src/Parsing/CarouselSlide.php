<?php

namespace Plasticode\Parsing;

class CarouselSlide
{
    private string $src;
    private ?string $caption = null;

    public function __construct(string $src)
    {
        $this->src = $src;
    }

    public function src() : string
    {
        return $this->src;
    }

    public function caption() : ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption) : void
    {
        $this->caption = $caption;
    }
}
