<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;

final class ArraysTest extends TestCase
{
    /**
     * @covers Arrays
     * @dataProvider existsProvider
     * 
     * @param mixed $key
     */
    public function testExists(?array $array, $key, bool $result)
    {
        $this->assertEquals($result, Arrays::exists($array, $key));
    }

    public function existsProvider()
    {
        return [
            [null, null, false],
            [null, 'a', false],
            [[], null, false],
            [[], 'a', false],
            [['a', 'b', 'c'], null, false],
            [['a', 'b', 'c'], 'a', false],
            [['a', 'b', 'c'], 1, true],
            [['a' => 'av', 'b' => 'bv', 'c' => 'cv'], null, false],
            [['a' => 'av', 'b' => 'bv', 'c' => 'cv'], 'b', true],
            [['a' => 'av', 'b' => 'bv', 'c' => 'cv'], 'd', false],
        ];
    }
}