<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Parsing\Mappers\UrlMapper;
use Plasticode\Parsing\TagMapperSource;

class BBParserConfig extends TagMapperSource
{
    public function __construct()
    {
        // $this->register('img', new ImageMapper(), 'image');
        // $this->register('leftimg', new ImageMapper(), 'image');
        // $this->register('rightimg', new ImageMapper(), 'image');
        // $this->register('carousel', new CarouselMapper());
        // $this->register('youtube', new YoutubeMapper());
        // $this->register('color', new ColorMapper());
        $this->register('url', new UrlMapper());
    }
}
