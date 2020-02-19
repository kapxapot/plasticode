<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Parsing\TagMappers\CarouselMapper;
use Plasticode\Parsing\TagMappers\ColorMapper;
use Plasticode\Parsing\TagMappers\ImageMapper;
use Plasticode\Parsing\TagMappers\UrlMapper;
use Plasticode\Parsing\TagMappers\YoutubeMapper;
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
