<?php

namespace Plasticode\Tests\Parsing\BB;

use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;
use Plasticode\Tests\BaseRenderTestCase;

final class BBContainerParserTest extends BaseRenderTestCase
{
    /** @var BBContainerParser */
    private $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->parser = new BBContainerParser(
            new BBContainerConfig(),
            new BBSequencer(),
            new BBTreeBuilder(),
            new BBTreeRenderer($this->renderer)
        );
    }

    protected function tearDown() : void
    {
        unset($this->parser);

        parent::tearDown();
    }

    /**
     * Renders BB containers from $text.
     *
     * @param string $text
     * @return string
     */
    private function render(string $text) : string
    {
        $context = $this->parser->parse($text);
        return $context->text;
    }

    public function testRenderList() : void
    {
        $text = $this->render(
            '[list][*]one[*]two[*]three[/list]'
        );

        $this->assertEquals(
            '<ul><li>one</li><li>two</li><li>three</p></li></ul>',
            $text
        );
    }

    public function testRenderOrderedList() : void
    {
        $text = $this->render(
            '[list=1][*]one[*]two[*]three[/list]'
        );

        $this->assertEquals(
            '<ol><li>one</li><li>two</li><li>three</p></li></ol>',
            $text
        );
    }

    public function testRenderQuote() : void
    {
        $text = $this->render(
            '[quote|author|http://someurl|date|other chunk]text[/quote]'
        );

        $this->assertNotEmpty($text);
    }

    public function testRenderSpoiler() : void
    {
        $text = $this->render(
            '[spoiler|Super spoiler]Hidden text[/spoiler]'
        );

        $this->assertNotEmpty($text);
    }

    public function testRenderDanglingEnds() : void
    {
        $text = $this->render(
            '[/spoiler]'
        );

        $this->assertEquals('<p>[/spoiler]</p>', $text);
    }

    public function testBuildDanglingStarts() : void
    {
        $text = $this->render(
            '[spoiler]'
        );

        $this->assertEquals('<p>[spoiler]</p>', $text);
    }
}
