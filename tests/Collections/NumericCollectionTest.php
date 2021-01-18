<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Generic\NumericCollection;

final class NumericCollectionTest extends TestCase
{
    public function testMaxInt() : void
    {
        $col = NumericCollection::collect(1, 2, 3, 5, 7, 2);

        $this->assertEquals(7, $col->max());
    }

    public function testMaxOnEmptyArrayProducesNull() : void
    {
        $col = NumericCollection::empty();

        $this->assertNull($col->max());
    }

    public function testDistinctInt() : void
    {
        $col = NumericCollection::collect(1, 2, 3, 5, 7, 2);

        $this->assertEquals(
            [1, 2, 3, 5, 7],
            $col->distinct()->toArray()
        );
    }

    public function testSumInt() : void
    {
        $col = NumericCollection::collect(1, 2, 3, 4, 5);

        $this->assertEquals(15, $col->sum());
    }

    public function testSumFloat() : void
    {
        $col = NumericCollection::collect(1.1, 2.2, 3.3, 4.4, 5.5);

        $this->assertEquals(16.5, $col->sum());
    }

    public function testSumMixed() : void
    {
        $col = NumericCollection::collect(1, '2', 3.5);

        $this->assertEquals(6.5, $col->sum());
    }

    public function testSumEmptyMustBeZero() : void
    {
        $col = NumericCollection::empty();

        $this->assertEquals(0, $col->sum());
    }
}
