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

    public function testParse() : void
    {
        $context = $this->parser->parse('[url=http://warcry.ru]Warcry.ru[/url]');

        $this->assertEquals(
            '<a href="http://warcry.ru">Warcry.ru</a>',
            $context->text
        );
    }
}
