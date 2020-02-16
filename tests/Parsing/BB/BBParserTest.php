<?php

namespace Plasticode\Tests\Parsing\BB;

use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Tests\BaseRenderTestCase;
use Plasticode\Tests\Mocks\LinkerMock;

final class BBParserTest extends BaseRenderTestCase
{
    /** @var BBParser */
    private $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $linker = new LinkerMock();
        $config = new BBParserConfig($linker);

        $this->parser = new BBParser($config, $this->renderer);
    }

    protected function tearDown() : void
    {
        unset($this->parser);

        parent::tearDown();
    }

    public function testYoutube() : void
    {
        $context = $this->parser->parse('[youtube]somecode[/youtube]');

        $this->assertEquals(
            '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/somecode" frameborder="0" allowfullscreen></iframe></div>',
            $context->text
        );
    }

    public function testColor() : void
    {
        $context = $this->parser->parse('[color=#ffaa00]Colored text[/color]');

        $this->assertEquals(
            '<span style="color: #ffaa00">Colored text</span>',
            $context->text
        );
    }

    public function testUrl() : void
    {
        $context = $this->parser->parse('[url=http://warcry.ru]Warcry.ru[/url]');

        $this->assertEquals(
            '<a href="http://warcry.ru">Warcry.ru</a>',
            $context->text
        );
    }

    public function testIncorrect() : void
    {
        $context = $this->parser->parse('[url=http://warcry.ru]Warcry.ru[/color]');

        $this->assertEquals(
            '[url=http://warcry.ru]Warcry.ru[/color]',
            $context->text
        );
    }
}
