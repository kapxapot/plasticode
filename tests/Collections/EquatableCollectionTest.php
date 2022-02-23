<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\CollectionDummy;
use Plasticode\Testing\Dummies\ModelDummy;

final class EquatableCollectionTest extends TestCase
{
    public function testExceptOne(): void
    {
        $m1 = new ModelDummy(1, 'one');
        $m2 = new ModelDummy(2, 'two');
        $m3 = new ModelDummy(3, 'three');

        $col = CollectionDummy::collect($m1, $m2, $m3);

        $result = $col->except($m2);

        $this->assertInstanceOf(CollectionDummy::class, $result);
        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->equals($m1));
        $this->assertTrue($result[1]->equals($m3));
    }

    public function testExceptMany(): void
    {
        $m1 = new ModelDummy(1, 'one');
        $m2 = new ModelDummy(2, 'two');
        $m3 = new ModelDummy(3, 'three');

        $col1 = CollectionDummy::collect($m1, $m2);
        $col2 = CollectionDummy::collect($m2, $m3);

        $result = $col1->except($col2);

        $this->assertInstanceOf(CollectionDummy::class, $result);
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->equals($m1));
    }

    public function testIntersect(): void
    {
        $m1 = new ModelDummy(1, 'one');
        $m2 = new ModelDummy(2, 'two');
        $m3 = new ModelDummy(3, 'three');

        $col1 = CollectionDummy::collect($m1, $m2);
        $col2 = CollectionDummy::collect($m2, $m3);

        $result = $col1->intersect($col2);

        $this->assertInstanceOf(CollectionDummy::class, $result);
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->equals($m2));
    }
}
