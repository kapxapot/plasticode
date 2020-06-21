<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Testing\Dummies\CollectionDummy;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Testing\Dummies\InvalidTypedCollection;

final class TypedCollectionTest extends TestCase
{
    public function testCreateAndFilter() : void
    {
        $typed = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $this->assertInstanceOf(CollectionDummy::class, $typed);
        $this->assertCount(2, $typed);

        $filtered = $typed->where(
            fn (ModelDummy $dm) => $dm->id == 2
        );

        $this->assertInstanceOf(CollectionDummy::class, $filtered);
        $this->assertCount(1, $filtered);
    }

    public function testFrom() : void
    {
        $col = Collection::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $typed = CollectionDummy::from($col);

        $this->assertInstanceOf(CollectionDummy::class, $typed);
        $this->assertCount(2, $typed);
    }

    public function testInvalidData() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $dc = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                'two',
                2,
            ]
        );
    }

    public function testInvalidCollection() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $dc = InvalidTypedCollection::empty();
    }
}
