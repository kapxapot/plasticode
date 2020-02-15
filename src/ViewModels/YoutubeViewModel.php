<?php

namespace Plasticode\ViewModels;

class YoutubeViewModel extends ViewModel
{
    /** @var string */
    private $code;

    /** @var integer */
    private $width;

    /** @var integer */
    private $height;

    public function __construct(string $code, int $width, int $height)
    {
        $this->code = $code;
        $this->width = $width;
        $this->height = $height;
    }

    public function code() : string
    {
        return $this->code;
    }

    public function width() : int
    {
        return $this->width;
    }

    public function height() : int
    {
        return $this->height;
    }
}
