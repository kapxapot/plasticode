<?php

namespace Plasticode\Tests\IO;

use PHPUnit\Framework\TestCase;
use Plasticode\IO\File;

final class FileTest extends TestCase
{
    public function testAbsolutePath() : void
    {
        $this->assertEquals(
            'C:\xampp\htdocs\projects\associ\src\..\logs\app.log',
            File::absolutePath(
                'C:\xampp\htdocs\projects\associ\src',
                '\..\logs\app.log'
            )
        );
    }
}
