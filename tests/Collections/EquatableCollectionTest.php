<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\CollectionDummy;
use Plasticode\Testing\Dummies\ModelDummy;

final class EquatableCollectionTest extends TestCase
{
    public function testIntersect(): void
    {
        $m1 = new ModelDummy(1, 'one');
        $m2 = new ModelDummy(2, 'two');
        $m3 = new ModelDummy(3, 'three');

        $col1 = CollectionDummy::collect($m1, $m2);
        $col2 = CollectionDummy::collect($m2, $m3);

        $inter = $col1->intersect($col2);

        $this->assertInstanceOf(CollectionDummy::class, $inter);
        $this->assertCount(1, $inter);
        $this->assertEquals($m2->id, $inter[0]->id);
    }
}
