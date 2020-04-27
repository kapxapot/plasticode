<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

final class SortTest extends TestCase
{
    public function testOrderBy() : void
    {
        $array = [
            ['a' => 2],
            ['a' => 1],
            ['a' => 3],
        ];

        $this->assertEquals(
            [
                ['a' => 1],
                ['a' => 2],
                ['a' => 3],
            ],
            Arrays::orderBy($array, 'a')
        );
    }

    public function testOrderByDesc() : void
    {
        $array = [
            ['a' => 2],
            ['a' => 1],
            ['a' => 3],
        ];

        $this->assertEquals(
            [
                ['a' => 3],
                ['a' => 2],
                ['a' => 1],
            ],
            Arrays::orderByDesc($array, 'a')
        );
    }

    public function testOrderByStr() : void
    {
        $array = [
            ['name' => 'Anna'],
            ['name' => 'Peter'],
            ['name' => 'John'],
        ];

        $this->assertEquals(
            [
                ['name' => 'Anna'],
                ['name' => 'John'],
                ['name' => 'Peter'],
            ],
            Arrays::orderByStr($array, 'name')
        );
    }

    public function testOrderByStrDesc() : void
    {
        $array = [
            ['name' => 'Anna'],
            ['name' => 'Peter'],
            ['name' => 'John'],
        ];

        $this->assertEquals(
            [
                ['name' => 'Peter'],
                ['name' => 'John'],
                ['name' => 'Anna'],
            ],
            Arrays::orderByStrDesc($array, 'name')
        );
    }

    public function testMultiSort() : void
    {
        $array = [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Peter'],
            ['id' => 3, 'name' => 'Alex'],
        ];

        $steps = [
            SortStep::asc('name', Sort::STRING),
            SortStep::desc('id'),
        ];

        $this->assertEquals(
            [
                ['id' => 3, 'name' => 'Alex'],
                ['id' => 1, 'name' => 'Alex'],
                ['id' => 2, 'name' => 'Peter'],
            ],
            Arrays::multiSort($array, $steps)
        );
    }
}
