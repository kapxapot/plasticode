<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Factories\PageLinkMapperFactory;
use Plasticode\Tests\Mocks\LinkerMock;

final class PageLinkMapperTest extends BaseRenderTestCase
{
    /** @var PageLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();
        $tagLinkMapper = new TagLinkMapper($this->renderer, $linker);

        $this->mapper = PageLinkMapperFactory::make(
            $this->renderer,
            $linker,
            $tagLinkMapper
        );
    }

    protected function tearDown() : void
    {
        unset($this->mapper);

        parent::tearDown();
    }

    /**
     * @dataProvider mapProvider
     */
    public function testMap(array $chunks, ?string $expected) : void
    {
        $this->assertEquals(
            $expected,
            $this->mapper->map($chunks)
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
            [
                ['warcraft'],
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>'
            ]
        ];
    }

    /**
     * @dataProvider renderLinksProvider
     *
     * @param string $original
     * @param string $expected
     * @return void
     */
    public function testRenderLinks(string $original, string $expected) : void
    {
        $context = ParsingContext::fromText($original);
        $renderedContext = $this->mapper->renderLinks($context);

        $this->assertEquals(
            $expected,
            $renderedContext->text
        );
    }

    public function renderLinksProvider() : array
    {
        $linker = new LinkerMock();

        return [
            [
                '<a href="%page%/about-us" class="entity-url">about us</a>',
                '<a href="' . $linker->page() . 'about-us" class="entity-url">about us</a>'
            ],
            [
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                '<a href="' . $linker->tag() . 'warcraft" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
