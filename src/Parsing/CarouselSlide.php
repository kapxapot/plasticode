<?php

namespace Plasticode\Parsing;

class CarouselSlide
{
    /** @var string */
    private $src;

    /** @var string|null */
    private $caption;

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
