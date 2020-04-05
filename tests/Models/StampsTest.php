<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Models\User;
use Plasticode\Testing\Dummies\StampsDummy;
use Plasticode\Util\Date;

final class StampsTest extends TestCase
{
    public function testStamps() : void
    {
        $userId = 1;
        $date = Date::dbNow();

        $user = User::create(
            [
                'id' => $userId,
            ]
        );

        /** @var StampsDummy */
        $dummy = StampsDummy::create(
            [
                'created_at' => $date,
                'created_by' => $userId,
                'updated_at' => $date,
                'updated_by' => $userId,
            ]
        );
        
        $dummy = $dummy
            ->withCreator($user)
            ->withUpdater($user);

        $this->assertEquals($date, $dummy->createdAt);
        $this->assertEquals(Date::iso($date), $dummy->createdAtIso());
        $this->assertEquals($userId, $dummy->createdBy);
        $this->assertEquals($user->getId(), $dummy->creator()->getId());
        $this->assertTrue($dummy->creator()->equals($user));

        $this->assertEquals($date, $dummy->updatedAt);
        $this->assertEquals(Date::iso($date), $dummy->updatedAtIso());
        $this->assertEquals($userId, $dummy->updatedBy);
        $this->assertEquals($user->getId(), $dummy->updater()->getId());
        $this->assertTrue($dummy->updater()->equals($user));
    }
}
