<?php

namespace Plasticode\Testing\Factories;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Testing\Mocks\LinkerMock;
use Plasticode\Testing\Mocks\LinkMappers\GenericLinkMapperMock;

class DoubleBracketsConfigFactory
{
    public static function make(RendererInterface $renderer): DoubleBracketsConfig
    {
        $linker = new LinkerMock();

        $tagLinkMapper = new TagLinkMapper($renderer, $linker);

        $pageLinkMapper = PageLinkMapperFactory::make(
            $renderer,
            $linker,
            $tagLinkMapper
        );

        return (new DoubleBracketsConfig())
            ->setDefaultMapper($pageLinkMapper)
            ->registerTaggedMappers(
                new NewsLinkMapper($renderer, $linker),
                $tagLinkMapper
            )
            ->setGenericMapper(new GenericLinkMapperMock());
    }
}
