<?php

namespace Plasticode\ViewModels;

class ImageViewModel extends ViewModel
{
    /** @var string */
    private $tag;

    /** @var string */
    private $source;

    /** @var string|null */
    private $thumb;

    /** @var string|null */
    private $alt;

    /** @var integer */
    private $width;

    /** @var integer */
    private $height;

    /** @var string|null */
    private $url;

    public function __construct(string $tag, string $source, ?string $thumb, ?string $alt, int $width, int $height, ?string $url)
    {
        $this->tag = $tag;
        $this->source = $source;
        $this->thumb = $thumb;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
        $this->url = $url;
    }

    public function tag() : string
    {
        return $this->tag;
    }

    public function source() : string
    {
        return $this->source;
    }

    public function thumb() : ?string
    {
        return $this->thumb;
    }

    public function alt() : ?string
    {
        return $this->alt;
    }

    public function width() : int
    {
        return $this->width;
    }

    public function height() : int
    {
        return $this->height;
    }

    public function url() : ?string
    {
        return $this->url;
    }
}
