<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Mocks\LinkerMock;
use Plasticode\Tests\Mocks\Repositories\PageRepositoryMock;
use Plasticode\Tests\Mocks\Repositories\TagRepositoryMock;
use Plasticode\Util\Text;

final class DoubleBracketsParserTest extends BaseRenderTestCase
{
    /** @var LinkerMock */
    private $linker;

    /** @var DoubleBracketsParser */
    private $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->linker = new LinkerMock();

        $config = new DoubleBracketsConfig(
            new PageRepositoryMock(),
            new TagRepositoryMock(),
            $this->renderer,
            $this->linker
        );

        $this->parser = new DoubleBracketsParser($config);
    }

    protected function tearDown() : void
    {
        unset($this->parser);
        unset($this->linker);

        parent::tearDown();
    }

    public function testParse() : void
    {
        $context = ParsingContext::fromLines(
            [
                '[[Illidan Stormrage]]',
                '[[illidan-stormrage|Illidanchick]]',
                '[[about us]]',
                '[[warcraft]]',
                '[[tag:about us]]',
                '[[tag:About us]]',
                '[[tag:about us|About us]]',
                '[[tag:warcraft]]',
                '[[news:123]]',
                '[[news:5|Some great news!]]',
            ]
        );

        $parsedContext = $this->parser->parseContext($context);

        $this->assertEquals(
            Text::fromLines(
                [
                    '<span class="no-url">Illidan Stormrage</span>',
                    '<span class="no-url" data-toggle="tooltip" title="illidan-stormrage">Illidanchick</span>',
                    '<a href="%page%/about-us" class="entity-url">about us</a>',
                    '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                    '<a href="%tag%/about+us" class="entity-url">about us</a>',
                    '<a href="%tag%/About+us" class="entity-url">About us</a>',
                    '<a href="%tag%/about+us" class="entity-url">About us</a>',
                    '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                    '<a href="%news%/123" class="entity-url">123</a>',
                    '<a href="%news%/5" class="entity-url">Some great news!</a>',
                ]
            ),
            $parsedContext->text
        );
    }

    public function testRenderLinks() : void
    {
        $context = ParsingContext::fromLines(
            [
                '<a href="%page%/about-us" class="entity-url">about us</a>',
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                '<a href="%news%/5" class="entity-url">Some great news!</a>',
            ]
        );

        $parsedContext = $this->parser->renderLinks($context);

        $this->assertEquals(
            Text::fromLines(
                [
                    '<a href="' . $this->linker->page() . 'about-us" class="entity-url">about us</a>',
                    '<a href="' . $this->linker->tag() . 'warcraft" class="entity-url">warcraft</a>',
                    '<a href="' . $this->linker->news() . '5" class="entity-url">Some great news!</a>',
                ]
            ),
            $parsedContext->text
        );
    }
}
