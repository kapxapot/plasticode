<?php

namespace Plasticode\ViewModels;

class CarouselViewModel extends ViewModel
{
    /** @var string */
    private $id;

    /** @var CarouselSlide[] */
    private $slides;

    /**
     * @param string $id
     * @param CarouselSlide[] $slides
     */
    public function __construct(string $id, array $slides)
    {
        $this->id = $id;
        $this->slides = $slides;
    }

    public function id() : string
    {
        return $this->id;
    }

    /**
     * @return CarouselSlide[]
     */
    public function slides() : array
    {
        return $this->slides;
    }
}
