<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Testing\Dummies\DummyCollection;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Testing\Dummies\InvalidTypedCollection;

final class TypedCollectionTest extends TestCase
{
    public function testCreateAndFilter() : void
    {
        $typed = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $this->assertInstanceOf(DummyCollection::class, $typed);
        $this->assertCount(2, $typed);

        $filtered = $typed->where(
            fn (DummyModel $dm) => $dm->id == 2
        );

        $this->assertInstanceOf(DummyCollection::class, $filtered);
        $this->assertCount(1, $filtered);
    }

    public function testFrom() : void
    {
        $col = Collection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $typed = DummyCollection::from($col);

        $this->assertInstanceOf(DummyCollection::class, $typed);
        $this->assertCount(2, $typed);
    }

    public function testInvalidData() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $dc = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
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
