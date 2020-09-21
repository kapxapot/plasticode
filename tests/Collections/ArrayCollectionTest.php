<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\ArrayCollection;

final class ArrayCollectionTest extends TestCase
{
    public function testCollect() : void
    {
        $col = ArrayCollection::collect(
            [1, 2, 3],
            ['abc', 'def']
        );

        $this->assertCount(2, $col);
        $this->assertIsArray($col[0]);
        $this->assertIsArray($col[1]);
    }

    public function testFailForIncorrectData() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArrayCollection::collect(
            [1, 2, 3],
            'abc',
            5
        );
    }
}
