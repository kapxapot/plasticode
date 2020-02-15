<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Parsing\Mappers\CarouselMapper;
use Plasticode\Parsing\Mappers\ColorMapper;
use Plasticode\Parsing\Mappers\ImageMapper;
use Plasticode\Parsing\Mappers\UrlMapper;
use Plasticode\Parsing\Mappers\YoutubeMapper;
use Plasticode\Parsing\TagMapperSource;

class BBParserConfig extends TagMapperSource
{
    public function __construct(LinkerInterface $linker)
    {
        $this->register('img', new ImageMapper(), 'image');
        $this->register('leftimg', new ImageMapper(), 'image');
        $this->register('rightimg', new ImageMapper(), 'image');
        $this->register('carousel', new CarouselMapper());
        $this->register('youtube', new YoutubeMapper($linker));
        $this->register('color', new ColorMapper());
        $this->register('url', new UrlMapper());
    }
}
