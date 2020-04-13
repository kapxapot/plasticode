<?php

namespace Plasticode\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Plasticode\Traits\Frozen;

final class FrozenTest extends TestCase
{
    use Frozen;

    private static int $i;

    protected function setUp() : void
    {
        self::$i = 0;
    }

    public function testOnce() : void
    {
        $once = $this->frozen(
            fn () => ++self::$i
        );

        $this->assertEquals(1, $once());
        $this->assertEquals(1, $once());
    }

    public function testTwice() : void
    {
        $manyTimes = fn () => ++self::$i;

        $this->assertEquals(1, $manyTimes());
        $this->assertEquals(2, $manyTimes());
    }
}
