<?php

namespace Plasticode\Tests\Parsing\LinkMappers;

use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Mocks\LinkerMock;
use Plasticode\Tests\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;

final class PageLinkMapperTest extends BaseRenderTestCase
{
    /** @var LinkerMock */
    private $linker;

    /** @var PageLinkMapper */
    private $mapper;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->linker = new LinkerMock();

        $this->mapper = new PageLinkMapper(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $this->renderer,
            $this->linker,
            new TagLinkMapper($this->renderer, $this->linker)
        );
    }

    protected function tearDown() : void
    {
        unset($this->mapper);
        unset($this->linker);

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

    public function testRenderLinks() : void
    {
        $context = ParsingContext::fromText(
            '<a href="%page%/about-us" class="entity-url">about us</a>'
        );

        $renderedContext = $this->mapper->renderLinks($context);

        $this->assertEquals(
            '<a href="' . $this->linker->page() . 'about-us" class="entity-url">about us</a>',
            $renderedContext->text
        );
    }
}
