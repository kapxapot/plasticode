<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\ScalarCollection;

final class ScalarCollectionTest extends TestCase
{
    public function testMaxInt() : void
    {
        $col = ScalarCollection::make([1, 2, 3, 5, 7, 2]);

        $this->assertEquals(7, $col->max());
    }

    public function testMaxStr() : void
    {
        $col = ScalarCollection::make(['abc', 'bde', 'xyz']);

        $this->assertEquals('xyz', $col->max());
    }

    public function testDistinctInt() : void
    {
        $col = ScalarCollection::make([1, 2, 3, 5, 7, 2]);

        $this->assertEquals([1, 2, 3, 5, 7], $col->distinct()->toArray());
    }

    public function testEmptyArrayProducesNull() : void
    {
        $col = ScalarCollection::empty();

        $this->assertNull($col->max());
    }
}
