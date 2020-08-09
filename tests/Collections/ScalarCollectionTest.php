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

    public function testMaxOnEmptyArrayProducesNull() : void
    {
        $col = ScalarCollection::empty();

        $this->assertNull($col->max());
    }

    public function testDistinctInt() : void
    {
        $col = ScalarCollection::make([1, 2, 3, 5, 7, 2]);

        $this->assertEquals([1, 2, 3, 5, 7], $col->distinct()->toArray());
    }

    public function testSumInt() : void
    {
        $col = ScalarCollection::make([1, 2, 3, 4, 5]);

        $this->assertEquals(15, $col->sum());
    }

    public function testSumFloat() : void
    {
        $col = ScalarCollection::make([1.1, 2.2, 3.3, 4.4, 5.5]);

        $this->assertEquals(16.5, $col->sum());
    }

    public function testSumStringsMustBeZero() : void
    {
        $col = ScalarCollection::make(['abc', 'def', 'ghi']);

        $this->assertEquals(0, $col->sum());
    }

    public function testSumMixed() : void
    {
        $col = ScalarCollection::make(['abc', '2', 3.5]);

        $this->assertEquals(5.5, $col->sum());
    }

    public function testSumEmptyMustBeZero() : void
    {
        $col = ScalarCollection::empty();

        $this->assertEquals(0, $col->sum());
    }
}
