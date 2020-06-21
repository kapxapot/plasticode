<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DbModelDummy;
use Plasticode\Testing\Dummies\ModelDummy;
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
        $dummy = (new DbModelDummy())
            ->withDummy(
                new ModelDummy(1, 'one')
            );

        $this->assertInstanceOf(ModelDummy::class, $dummy->dummy());
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
        $dummy = (new DbModelDummy())
            ->withDummy(
                fn () => new ModelDummy(1, 'one')
            );

        $this->assertInstanceOf(ModelDummy::class, $dummy->dummy());
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

        $dummy = new DbModelDummy();
        $dummy->dummy();
    }

    public function testOnce() : void
    {
        $dummy = (new DbModelDummy())
            ->withDummy(
                $this->frozen(
                    fn () => new ModelDummy(++self::$id, 'some dummy')
                )
            );

        $this->assertEquals(1, $dummy->dummy()->id);
        $this->assertEquals(1, $dummy->dummy()->id);
    }

    public function testTwice() : void
    {
        $dummy = (new DbModelDummy())
            ->withDummy(
                fn () => new ModelDummy(++self::$id, 'some dummy')
            );

        $this->assertEquals(1, $dummy->dummy()->id);
        $this->assertEquals(2, $dummy->dummy()->id);
    }

    public function testAlias() : void
    {
        $dummy = new DbModelDummy();

        $this->assertEquals('db_model_dummies', $dummy::pluralAlias());
        $this->assertEquals('db_model_dummies', DbModelDummy::pluralAlias());
    }
}
