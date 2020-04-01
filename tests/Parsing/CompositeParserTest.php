<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Testing\Factories\LinkMapperSourceFactory;
use Plasticode\Testing\Mocks\LinkerMock;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Util\Text;

final class CompositeParserTest extends BaseRenderTestCase
{
    public function testRenderLinks() : void
    {
        $linker = new LinkerMock();
        $config = LinkMapperSourceFactory::make($this->renderer);

        $parser = new CompositeParser(
            new DoubleBracketsParser($config)
        );

        $context = ParsingContext::fromLines(
            [
                '<a href="%page%/about-us" class="entity-url">about us</a>',
                '<a href="%tag%/warcraft" class="entity-url">warcraft</a>',
                '<a href="%news%/5" class="entity-url">Some great news!</a>',
            ]
        );

        $parsedContext = $parser->renderLinks($context);

        $this->assertEquals(
            Text::fromLines(
                [
                    '<a href="' . $linker->page() . 'about-us" class="entity-url">about us</a>',
                    '<a href="' . $linker->tag() . 'warcraft" class="entity-url">warcraft</a>',
                    '<a href="' . $linker->news() . '5" class="entity-url">Some great news!</a>',
                ]
            ),
            $parsedContext->text
        );
    }
}
