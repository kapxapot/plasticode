<?php

namespace Plasticode\Testing\Factories;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Testing\Mocks\LinkerMock;
use Plasticode\Testing\Mocks\LinkMappers\GenericLinkMapperMock;

class LinkMapperSourceFactory
{
    public static function make(RendererInterface $renderer): LinkMapperSource
    {
        $linker = new LinkerMock();

        $tagLinkMapper = new TagLinkMapper($renderer, $linker);

        $pageLinkMapper = PageLinkMapperFactory::make(
            $renderer,
            $linker,
            $tagLinkMapper
        );

        return (new LinkMapperSource())
            ->setDefaultMapper($pageLinkMapper)
            ->registerTaggedMappers(
                new NewsLinkMapper($renderer, $linker),
                $tagLinkMapper
            )
            ->setGenericMapper(new GenericLinkMapperMock());
    }
}
