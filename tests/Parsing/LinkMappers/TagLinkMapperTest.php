<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Testing\Mocks\LinkerMock;
use Plasticode\Tests\BaseRenderTestCase;

final class TagLinkMapperTest extends BaseRenderTestCase
{
    /** @var TagLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new TagLinkMapper($this->renderer, $linker);
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
                ['tag:about us'],
                '<a href="%tag%/about+us" class="entity-url">about us</a>'
            ],
            [
                ['tag:About us'],
                '<a href="%tag%/About+us" class="entity-url">About us</a>'
            ],
            [
                ['tag:about us', 'About us'],
                '<a href="%tag%/about+us" class="entity-url">About us</a>'
            ],
            [
                ['tag:warcraft'],
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
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                '<a href="' . $linker->tag() . 'warcraft" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
