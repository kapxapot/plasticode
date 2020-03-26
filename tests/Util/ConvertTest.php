<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Convert;

final class ConvertTest extends TestCase
{
    /**
     * @dataProvider toBitProvider
     */
    public function testToBit(?bool $source, int $expected) : void
    {
        $actual = Convert::toBit($source);

        $this->assertEquals($expected, $actual);
    }

    public function toBitProvider() : array
    {
        return [
            [true, 1],
            [false, 0],
            [null, 0],
        ];
    }

    /**
     * @dataProvider fromBitProvider
     */
    public function testFromBit(?int $source, bool $expected) : void
    {
        $actual = Convert::fromBit($source);

        $this->assertEquals($expected, $actual);
    }

    public function fromBitProvider() : array
    {
        return [
            [null, false],
            [0, false],
            [1, true],
        ];
    }

    /**
     * @dataProvider fromBitExceptionProvider
     */
    public function testFromBitException(int $source) : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Convert::fromBit($source);
    }

    public function fromBitExceptionProvider() : array
    {
        return [
            [-100],
            [-1],
            [2],
            [100],
        ];
    }
}
