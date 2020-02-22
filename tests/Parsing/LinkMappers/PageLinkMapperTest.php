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
    public function testMap(array $chunks, ?string $expected) : void
    {
        $linker = new LinkerMock();

        $mapper = new PageLinkMapper(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $this->renderer,
            $linker,
            new TagLinkMapper($this->renderer, $linker)
        );

        $this->assertEquals(
            $expected,
            $mapper->map($chunks)
        );
    }

    public function mapProvider() : array
    {
        return [
            [
                ['Illidan Stormrage'],
                '<span class="no-url">Illidan Stormrage</span>'
            ],
            [
                ['illidan-stormrage', 'Illidanchick'],
                '<span class="no-url" data-toggle="tooltip" title="illidan-stormrage">Illidanchick</span>'
            ],
            [
                ['about us'],
                '<a href="%page%/about-us" class="entity-url">about us</a>'
            ],
        ];
    }
}
