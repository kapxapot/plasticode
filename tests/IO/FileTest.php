<?php

namespace Plasticode\Tests\IO;

use PHPUnit\Framework\TestCase;
use Plasticode\IO\File;

final class FileTest extends TestCase
{
    /**
     * @dataProvider combineProvider
     */
    public function testCombine(
        string $part1,
        string $part2,
        string $expected
    ) : void
    {
        $this->assertEquals(
            $expected,
            File::combine($part1, $part2)
        );
    }

    public function combineProvider() : array
    {
        return [
            'winRelativeSlash' => [
                'C:\\xampp\\src',
                '\\..\\logs\\app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'winRelativeNoSlash' => [
                'C:\\xampp\\src',
                '..\\logs\\app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'unixRelativeSlash' => [
                '/xampp/src',
                '/../logs/app.log',
                '/xampp/src/../logs/app.log'
            ],
            'unixRelativeNoSlash' => [
                '/xampp/src',
                '../logs/app.log',
                '/xampp/src/../logs/app.log'
            ],
            'mixedRelativeSlash' => [
                'C:\\xampp\\src',
                '/../logs/app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'mixedRelativeNoSlash' => [
                'C:\\xampp\\src',
                '../logs/app.log',
                'C:/xampp/src/../logs/app.log'
            ],
        ];
    }

    /**
     * @dataProvider absolutePathProvider
     */
    public function testAbsolutePath(
        string $baseDir,
        string $relativePath,
        string $expected
    ) : void
    {
        $this->assertEquals(
            $expected,
            File::absolutePath($baseDir, $relativePath)
        );
    }

    public function absolutePathProvider() : array
    {
        return [
            'winRelativeSlash' => [
                'C:\\xampp\\src',
                '\\..\\logs\\app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'winRelativeNoSlash' => [
                'C:\\xampp\\src',
                '..\\logs\\app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'winAbsoluteUnchanged' => [
                'C:\\xampp\\src',
                '\\logs\\app.log',
                '\\logs\\app.log'
            ],
            'unixRelativeSlash' => [
                '/xampp/src',
                '/../logs/app.log',
                '/xampp/src/../logs/app.log'
            ],
            'unixRelativeNoSlash' => [
                '/xampp/src',
                '../logs/app.log',
                '/xampp/src/../logs/app.log'
            ],
            'unixAbsoluteUnchanged' => [
                '/xampp/src',
                '/logs/app.log',
                '/logs/app.log'
            ],
            'mixedRelativeSlash' => [
                'C:\\xampp\\src',
                '/../logs/app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'mixedRelativeNoSlash' => [
                'C:\\xampp\\src',
                '../logs/app.log',
                'C:/xampp/src/../logs/app.log'
            ],
            'mixedAbsoluteUnchanged' => [
                'C:\\xampp\\src',
                '/logs/app.log',
                '/logs/app.log'
            ],
        ];
    }

    /**
     * @dataProvider normalizePathProvider
     */
    public function testNormalizePath(string $original, string $expected) : void
    {
        $this->assertEquals(
            $expected,
            File::normalizePath($original)
        );
    }

    public function normalizePathProvider() : array
    {
        return [
            ['\\..\\logs\\app.log', '/../logs/app.log'],
            ['/../logs/app.log', '/../logs/app.log'],
        ];
    }
}
