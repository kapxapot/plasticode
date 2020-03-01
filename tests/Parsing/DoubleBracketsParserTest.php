<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Factories\LinkMapperSourceFactory;
use Plasticode\Tests\Mocks\LinkerMock;
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

        $config = LinkMapperSourceFactory::make($this->renderer);
        $this->parser = new DoubleBracketsParser($config);
    }

    protected function tearDown() : void
    {
        unset($this->parser);
        unset($this->linker);

        parent::tearDown();
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $original, string $expected) : void
    {
        $context = $this->parser->parse($original);

        $this->assertEquals(
            $expected,
            $context->text
        );
    }

    public function parseProvider() : array
    {
        return [
            [
                '[[]]', '[[]]'
            ],
            [
                '[[ ]]', '[[ ]]'
            ],
            [
                '[[Illidan Stormrage]]',
                '<span class="no-url">Illidan Stormrage</span>'
            ],
            [
                '[[illidan-stormrage|Illidanchick]]',
                '<span class="no-url" data-toggle="tooltip" title="illidan-stormrage">Illidanchick</span>'
            ],
            [
                '[[about us]]',
                '<a href="%page%/about-us" class="entity-url">about us</a>'
            ],
            [
                '[[warcraft]]',
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>'
            ],
            [
                '[[tag:about us]]',
                '<a href="%tag%/about+us" class="entity-url">about us</a>'
            ],
            [
                '[[tag:About us]]',
                '<a href="%tag%/About+us" class="entity-url">About us</a>'
            ],
            [
                '[[tag:about us|About us]]',
                '<a href="%tag%/about+us" class="entity-url">About us</a>'
            ],
            [
                '[[tag:warcraft]]',
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>'
            ],
            [
                '[[news:123]]',
                '<a href="%news%/123" class="entity-url">123</a>'
            ],
            [
                '[[news:5|Some great news!]]',
                '<a href="%news%/5" class="entity-url">Some great news!</a>'
            ],
            [
                '[[area:45|New area]]',
                '<a href="http://generic/area/45">New area</a>'
            ],
            [
                '[[about us|]]',
                '<a href="%page%/about-us" class="entity-url">about us</a>'
            ],
            [
                '[[about us| ]]',
                '<a href="%page%/about-us" class="entity-url">about us</a>'
            ]
        ];
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
