<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DummyDbModel;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Traits\Frozen;

final class DbModelTest extends TestCase
{
    use Frozen;

    private static int $id;

    protected function setUp() : void
    {
        self::$id = 0;
    }

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

    public function testOnce() : void
    {
        $dummy = (new DummyDbModel())
            ->withDummy(
                $this->frozen(
                    fn () => new DummyModel(++self::$id, 'some dummy')
                )
            );

        $this->assertEquals(1, $dummy->dummy()->id);
        $this->assertEquals(1, $dummy->dummy()->id);
    }

    public function testTwice() : void
    {
        $dummy = (new DummyDbModel())
            ->withDummy(
                fn () => new DummyModel(++self::$id, 'some dummy')
            );

        $this->assertEquals(1, $dummy->dummy()->id);
        $this->assertEquals(2, $dummy->dummy()->id);
    }

    public function testAlias() : void
    {
        $dummy = new DummyDbModel();

        $this->assertEquals('dummy_db_models', $dummy::pluralAlias());
        $this->assertEquals('dummy_db_models', DummyDbModel::pluralAlias());
    }
}
