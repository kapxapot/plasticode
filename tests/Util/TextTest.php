<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Text;

final class TextTest extends TestCase
{
    /**
     * @covers Text
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

    /** @covers Text */
    public function testNewLinesToBrs() : void
    {
        $this->assertEquals(
            'one<br/>two',
            Text::newLinesToBrs(
                Text::fromLines(['one', 'two'])
            )
        );
    }
}
