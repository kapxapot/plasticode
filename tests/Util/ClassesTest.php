<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Classes;
use Plasticode\ViewModels\SpoilerViewModel;

final class ClassesTest extends TestCase
{
    public function testGetPublicMethods() : void
    {
        $this->assertEquals(
            ['id', 'body', 'title', 'toArray', 'jsonSerialize'],
            Classes::getPublicMethods(SpoilerViewModel::class)
        );
    }
}
