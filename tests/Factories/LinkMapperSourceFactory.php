<?php

namespace Plasticode\Tests\Factories;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Tests\Mocks\GenericLinkMapperMock;
use Plasticode\Tests\Mocks\LinkerMock;
use Plasticode\Tests\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;

class LinkMapperSourceFactory
{
    public static function make(RendererInterface $renderer) : LinkMapperSource
    {
        $linker = new LinkerMock();

        $tagLinkMapper = new TagLinkMapper($renderer, $linker);

        $pageLinkMapper = new PageLinkMapper(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $renderer,
            $linker,
            $tagLinkMapper
        );

        $config = new LinkMapperSource();

        $config->setDefaultMapper($pageLinkMapper);

        $config->registerEntityMapper(new NewsLinkMapper($renderer, $linker));
        $config->registerEntityMapper($tagLinkMapper);

        $config->setGenericMapper(new GenericLinkMapperMock());

        return $config;
    }
}
