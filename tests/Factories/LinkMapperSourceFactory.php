<?php

namespace Plasticode\Tests\Factories;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Core\Interfaces\RendererInterface;
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

        $config = new DoubleBracketsConfig(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $renderer,
            $linker
        );

        $config->setGenericMapper(new GenericLinkMapperMock());

        return $config;
    }
}
