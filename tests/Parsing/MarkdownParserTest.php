<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\MarkdownParser;

final class MarkdownParserTest extends ParsingTestCase
{
    /** @var \Plasticode\Parsing\MarkdownParser */
    private $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->parser = new MarkdownParser($this->renderer);
    }

    protected function tearDown() : void
    {
        unset($this->parser);

        parent::tearDown();
    }

    protected function step() : ParsingStepInterface
    {
        return $this->parser;
    }

    /**
     * @covers MarkdownParser
     */
    public function testContextIsImmutable() : void
    {
        $this->assertContextIsImmutable();
    }

    /**
     * @covers MarkdownParser
     */
    public function testParseListUnordered() : void
    {
        $context = $this->parseLines(
            [
                '* I am',
                '* A cool',
                '* Markdown',
                '* List',
                '* Yay',
            ]
        );

        $this->assertEquals(
            '<ul><li>I am</li><li>A cool</li><li>Markdown</li><li>List</li><li>Yay</li></ul>',
            $context->text
        );
    }

    /**
     * @covers MarkdownParser
     */
    public function testParseListUnorderedMixed() : void
    {
        $context = $this->parseLines(
            [
                '* I am',
                '- Even',
                '+ More',
                '* Cooler',
            ]
        );

        $this->assertEquals(
            '<ul><li>I am</li><li>Even</li><li>More</li><li>Cooler</li></ul>',
            $context->text
        );
    }

    /**
     * @covers MarkdownParser
     */
    public function testParseListOrdered() : void
    {
        $context = $this->parseLines(
            [
                '1. Numbered',
                '2. Lists',
                '3. Work',
                '4. Too',
            ]
        );

        $this->assertEquals(
            '<ol><li>Numbered</li><li>Lists</li><li>Work</li><li>Too</li></ol>',
            $context->text
        );
    }

    /**
     * @covers MarkdownParser
     */
    public function testParseListOrderedJagged() : void
    {
        $context = $this->parseLines(
            [
                '1. In this list (1)',
                '3. Item numbers (3)',
                '6. Have gaps (6)',
            ]
        );

        $this->assertEquals(
            '<ol><li>In this list (1)</li><li>Item numbers (3)</li><li>Have gaps (6)</li></ol>',
            $context->text
        );
    }
}
