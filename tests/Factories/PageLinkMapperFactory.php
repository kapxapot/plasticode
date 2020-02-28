<?php

namespace Plasticode\Tests\Factories;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Tests\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;
use Plasticode\Tests\Seeders\PageSeeder;
use Plasticode\Tests\Seeders\TagSeeder;

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
