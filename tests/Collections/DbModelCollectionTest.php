<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Generic\DbModelCollection;

final class DbModelCollectionTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $col = DbModelCollection::empty();

        $this->assertNotNull($col);
    }
}
