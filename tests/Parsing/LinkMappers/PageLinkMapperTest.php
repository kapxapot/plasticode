<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Mocks\LinkerMock;
use Plasticode\Tests\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;

final class PageLinkMapperTest extends BaseRenderTestCase
{
    /**
     * @dataProvider mapProvider
     */
    public function testMap() : void
    {
        $mapper = new PageLinkMapper(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $this->renderer,
            new LinkerMock(),
            new TagLinkMapper()
        );

        $this->assertEquals(

        );
    }
}
