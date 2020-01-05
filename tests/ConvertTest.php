<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Convert;

final class ConvertTest extends TestCase
{
    /**
     * @covers Convert
     * @dataProvider toBitProvider
     */
    public function testToBit(?bool $source, int $expected) : void
    {
        $actual = Convert::toBit($source);

        $this->assertEquals($expected, $actual);
    }

    public function toBitProvider()
    {
        return [
            [true, 1],
            [false, 0],
            [null, 0],
        ];
    }
}
