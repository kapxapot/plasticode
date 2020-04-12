<?php

namespace Plasticode\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\OnceDummy;

final class OnceTest extends TestCase
{
    public function testOnce() : void
    {
        $dummy = new OnceDummy();

        $i = 0;

        $once = $dummy->once(
            fn () => ++$i
        );

        $this->assertEquals(1, $once());
        $this->assertEquals(1, $once());
    }
}
