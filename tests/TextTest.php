<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Text;

final class TextTest extends TestCase
{
    /**
     * @covers Text
     * @dataProvider toLinesProvider
     */
    public function testToLines(?string $original, array $expected): void
    {
        $this->assertEquals(
            Text::toLines($original),
            $expected
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
}
