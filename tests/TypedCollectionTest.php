<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DummyCollection;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Testing\Dummies\InvalidTypedCollection;

final class TypedCollectionTest extends TestCase
{
    public function testCreate() : void
    {
        $dc = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $this->assertCount(2, $dc);
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

        $dc = InvalidTypedCollection::make();
    }
}
