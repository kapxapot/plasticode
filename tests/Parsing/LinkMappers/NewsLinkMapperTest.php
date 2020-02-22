<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Mocks\LinkerMock;

final class NewsLinkMapperTest extends BaseRenderTestCase
{
    /** @var NewsLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $linker = new LinkerMock();

        $this->mapper = new NewsLinkMapper($this->renderer, $linker);
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
                ['news:123'],
                '<a href="%news%/123" class="entity-url">123</a>'
            ],
            [
                ['news:5', 'Some great news!'],
                '<a href="%news%/5" class="entity-url">Some great news!</a>'
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
                '<a href="%news%/123" class="entity-url">warcraft</a>',
                '<a href="' . $linker->news() . '123" class="entity-url">warcraft</a>'
            ]
        ];
    }
}
