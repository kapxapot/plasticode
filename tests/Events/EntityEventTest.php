<?php

namespace Plasticode\Tests\Events;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DbModelDummy;
use Plasticode\Testing\Events\DataEvent;
use Plasticode\Testing\Events\DbModelDummyEvent;

final class EntityEventTest extends TestCase
{
    public function testEntityId() : void
    {
        $e = new DbModelDummyEvent(
            new DbModelDummy(['id' => 1])
        );

        $o = new DbModelDummyEvent(
            new DbModelDummy()
        );

        $this->assertEquals(1, $e->getEntityId());
        $this->assertNull($o->getEntityId());
    }

    public function testEquals() : void
    {
        $e1 = new DbModelDummyEvent(
            new DbModelDummy(['id' => 1, 'name' => 'one'])
        );

        $e2 = new DbModelDummyEvent(
            new DbModelDummy(['id' => 1, 'name' => 'two'])
        );

        $e3 = new DbModelDummyEvent(
            new DbModelDummy(['id' => 2, 'name' => 'one'])
        );

        $o = new DataEvent('hello');

        $this->assertTrue($e1->equals($e2));
        $this->assertTrue($e2->equals($e1));
        $this->assertFalse($e1->equals($e3));
        $this->assertFalse($e1->equals(null));
        $this->assertFalse($e1->equals($o));
    }
}
