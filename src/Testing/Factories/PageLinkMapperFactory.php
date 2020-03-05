<?php

namespace Plasticode\Testing\Factories;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Testing\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Testing\Mocks\Repositories\TagRepositoryMock;
use Plasticode\Testing\Seeders\PageSeeder;
use Plasticode\Testing\Seeders\TagSeeder;

class PageLinkMapperFactory
{
    public static function make(
        RendererInterface $renderer,
        LinkerInterface $linker,
        LinkMapperInterface $tagLinkMapper
    ) : LinkMapperInterface
    {
        return new PageLinkMapper(
            new PageRepositoryMock(new PageSeeder()),
            new TagRepositoryMock(new TagSeeder()),
            $renderer,
            $linker,
            $tagLinkMapper
        );
    }
}
