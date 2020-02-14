<?php

namespace Plasticode\ViewModels;

class ColorViewModel extends ViewModel
{
    /** @var string */
    private $content;

    /** @var string|null */
    private $color;

    public function __construct(string $content, ?string $color)
    {
        $this->content = $content;
        $this->color = $color;
    }

    public function content() : string
    {
        return $this->content;
    }

    public function color() : ?string
    {
        return $this->color;
    }
}
