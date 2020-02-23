<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Text;

final class TextTest extends TestCase
{
    /**
     * @dataProvider toLinesProvider
     */
    public function testToLines(?string $original, array $expected) : void
    {
        $this->assertEquals(
            $expected,
            Text::toLines($original)
        );
    }

    public function toLinesProvider()
    {
        return [
            [null, []],
            ['', []],
            ['' . PHP_EOL . '', ['', '']],
            ['aba', ['aba']],
            ['aba' . PHP_EOL . 'baba', ['aba', 'baba']],
        ];
    }

    public function testNewLinesToBrs() : void
    {
        $this->assertEquals(
            'one<br/>two',
            Text::newLinesToBrs(
                Text::fromLines(['one', 'two'])
            )
        );
    }

    public function testTrimEmptyLines() : void
    {
        $this->assertEquals(
            ['one', '', 'two'],
            Text::trimEmptyLines(['', null, '', 'one', '', 'two', '', null])
        );
    }

    public function testTrimPattern() : void
    {
        $this->assertEquals(
            'dacacv',
            Text::trimPattern('a|b|c', 'bacdacacvacac')
        );
    }

    public function testTrimMultiPattern() : void
    {
        $this->assertEquals(
            'dacacv',
            Text::trimMultiPattern(['b', 'ac'], 'bacdacacvacac')
        );
    }

    public function testTrimBrs() : void
    {
        $this->assertEquals(
            'hey...',
            Text::trimBrs(
                '<br/><br /><br>hey...<br/><br>'
            )
        );
    }

    public function testTrimNewLinesAndBrs() : void
    {
        $this->assertEquals(
            'hey...',
            Text::trimNewLinesAndBrs(
                "\r\n\r<br/>\n<br /><br>hey...<br/><br>\r\n"
            )
        );
    }

    /**
     * @dataProvider toAbsoluteUrlsProvider
     *
     * @param string $text
     * @param string $baseUrl
     * @param string $expected
     * @return void
     */
    public function testToAbsoluteUrls(string $text, string $baseUrl, string $expected) : void
    {
        $this->assertEquals(
            $expected,
            Text::toAbsoluteUrls($text, $baseUrl)
        );
    }

    public function toAbsoluteUrlsProvider() : array
    {
        return [
            [
                '<a href="/some/url">aaa</a>',
                'https://warcry.ru/',
                '<a href="https://warcry.ru/some/url">aaa</a>'
            ],
            [
                '<a href=/some/url>aaa</a>',
                'https://warcry.ru',
                '<a href=https://warcry.ru/some/url>aaa</a>'
            ],
        ];
    }
}
