<?php

namespace Plasticode\Tests\Parsing\BB;

use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Testing\Mocks\LinkerMock;
use Plasticode\Tests\BaseRenderTestCase;

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

    public function testImg() : void
    {
        $context = $this->parser->parse('[img|http://thumb.ru/1.jpg|400|200|Some text]http://img.ru/img123[/img]');

        $this->assertEquals(
            '<div class="img"><a href="http://img.ru/img123" class="colorbox"><img src="http://thumb.ru/1.jpg" class="img-responsive" width="400" height="200" alt="Some text" title="Some text"/></a><div class="img-caption">Some text</div></div>',
            $context->text
        );
    }

    public function testImgBare() : void
    {
        $context = $this->parser->parse('[img]http://img.ru/img123[/img]');

        $this->assertEquals(
            '<div class="img"><img src="http://img.ru/img123" class="img-responsive" alt=""/></div>',
            $context->text
        );
    }

    public function testImgWithUrl() : void
    {
        $context = $this->parser->parse('[img|http://thumb.ru/1.jpg|//some.url|400|200|Some text]http://img.ru/img123[/img]');

        $this->assertEquals(
            '<div class="img"><a href="//some.url"><img src="http://thumb.ru/1.jpg" class="img-responsive" width="400" height="200" alt="Some text" title="Some text"/></a><div class="img-caption"><a href="//some.url">Some text</a></div></div>',
            $context->text
        );
    }

    public function testLeftImg() : void
    {
        $context = $this->parser->parse('[leftimg]http://img.ru/img123[/leftimg]');

        $this->assertEquals(
            '<figure class="img img-left"><img src="http://img.ru/img123" class="img-responsive" alt=""/></figure>',
            $context->text
        );
    }

    public function testRightImg() : void
    {
        $context = $this->parser->parse('[rightimg]http://img.ru/img123[/rightimg]');

        $this->assertEquals(
            '<figure class="img img-right"><img src="http://img.ru/img123" class="img-responsive" alt=""/></figure>',
            $context->text
        );
    }

    public function testCarousel() : void
    {
        $context = $this->parser->parseContext(
            ParsingContext::fromLines(
                [
                    '[carousel]',
                    'http://img.ru/1616 Some image',
                    '//some/other/link',
                    '[/carousel]',
                ]
            )
        );

        $this->assertStringStartsWith('<div id="carousel-', $context->text);
        $this->assertStringEndsWith('</div>', $context->text);
    }

    public function testYoutube() : void
    {
        $context = $this->parser->parse('[youtube]somecode[/youtube]');

        $this->assertEquals(
            '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/somecode" frameborder="0" allowfullscreen></iframe></div>',
            $context->text
        );
    }

    public function testYoutubeSized() : void
    {
        $context = $this->parser->parse('[youtube|320|200]somecode[/youtube]');

        $this->assertEquals(
            '<div class="center"><iframe src="https://www.youtube.com/embed/somecode" width="320" height="200" frameborder="0" allowfullscreen></iframe></div>',
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
