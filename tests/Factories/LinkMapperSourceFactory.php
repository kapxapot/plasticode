<?php

namespace Plasticode\Tests\Factories;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Tests\Mocks\GenericLinkMapperMock;
use Plasticode\Tests\Mocks\LinkerMock;

class LinkMapperSourceFactory
{
    public static function make(RendererInterface $renderer) : LinkMapperSource
    {
        $linker = new LinkerMock();

        $tagLinkMapper = new TagLinkMapper($renderer, $linker);

        $pageLinkMapper = PageLinkMapperFactory::make(
            $renderer,
            $linker,
            $tagLinkMapper
        );

        $config = new LinkMapperSource();

        $config->setDefaultMapper($pageLinkMapper);

        $config->registerTaggedMapper(new NewsLinkMapper($renderer, $linker));
        $config->registerTaggedMapper($tagLinkMapper);

        $config->setGenericMapper(new GenericLinkMapperMock());

        return $config;
    }
}
