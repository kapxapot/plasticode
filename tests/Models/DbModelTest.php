<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DummyDbModel;
use Plasticode\Testing\Dummies\DummyModel;

final class DbModelTest extends TestCase
{
    public function testWithObj() : void
    {
        $dummy = (new DummyDbModel())
            ->withDummy(
                new DummyModel(1, 'one')
            );

        $this->assertInstanceOf(DummyModel::class, $dummy->dummy());
        $this->assertEquals(
            [1, 'one'],
            [
                $dummy->dummy()->id,
                $dummy->dummy()->name
            ]
        );
    }

    public function testWithCallable() : void
    {
        $dummy = (new DummyDbModel())
            ->withDummy(
                fn () => new DummyModel(1, 'one')
            );

        $this->assertInstanceOf(DummyModel::class, $dummy->dummy());
        $this->assertEquals(
            [1, 'one'],
            [
                $dummy->dummy()->id,
                $dummy->dummy()->name
            ]
        );
    }

    public function testWithout() : void
    {
        $this->expectException(\BadMethodCallException::class);

        $dummy = new DummyDbModel();
        $dummy->dummy();
    }
}
