<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CutParser;

final class CutParserTest extends BaseParsingRenderTestCase
{
    /** @var ParsingStepInterface */
    private $cleanupParser;

    /** @var CutParser */
    private $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->cleanupParser = new CleanupParser(
            $this->config,
            $this->renderer
        );

        $this->parser = new CutParser($this->cleanupParser);
    }

    protected function tearDown() : void
    {
        unset($this->parser);

        parent::tearDown();
    }

    /**
     * @covers CutParser
     * @dataProvider parseWithCutProvider
     */
    public function testParseWithCut(string $text, string $fullExpected, string $shortExpected) : void
    {
        $this->assertEquals(
            $fullExpected,
            $this->parser->full($text)
        );

        $this->assertEquals(
            $shortExpected,
            $this->parser->short($text)
        );
    }

    public function parseWithCutProvider()
    {
        return [
            [
                '<p>Some text</p><p>with cut</p>[cut]<p>hehe</p>',
                '<p>Some text</p><p>with cut</p><p>hehe</p>',
                '<p>Some text</p><p>with cut</p>'
            ],
            [
                '<p>Some text</p><p>with cut<br/><br/>[cut]<br/><br/>hehe</p>',
                '<p>Some text</p><p>with cut</p><p>hehe</p>',
                '<p>Some text</p><p>with cut</p>'
            ],
            [
                '<p>Some text</p><p>with cut<br/>[cut]<br/>hehe</p>',
                '<p>Some text</p><p>with cut</p><p>hehe</p>',
                '<p>Some text</p><p>with cut</p>'
            ],
        ];
    }

    /**
     * @covers CutParser
     * @dataProvider parseWithoutCutProvider
     */
    public function testParseWithoutCut(?string $text) : void
    {
        $this->assertEquals(
            $text,
            $this->parser->full($text)
        );

        $this->assertNull($this->parser->short($text));
    }

    public function parseWithoutCutProvider()
    {
        return [
            ['<p>Some text</p><p>without cut</p>'],
            [''],
            [null],
        ];
    }

    /**
     * @covers CutParser
     */
    public function testParseAlternativeTag() : void
    {
        $parser = new CutParser($this->cleanupParser, '<!-- cut -->');

        $text = '<p>Some text</p><p>with cut</p><!-- cut --><p>hehe</p>';

        $this->assertEquals(
            '<p>Some text</p><p>with cut</p><p>hehe</p>',
            $parser->full($text)
        );

        $this->assertEquals(
            '<p>Some text</p><p>with cut</p>',
            $parser->short($text)
        );
    }
}
