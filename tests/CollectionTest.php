<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Collection;

final class CollectionTest extends TestCase
{
    /** @dataProvider flattenProvider */
    public function testFlatten(Collection $original, Collection $expected): void
    {
        $this->assertEquals($original->flatten()->toArray(), $expected->toArray());
    }

    public function flattenProvider()
    {
        return [
            [
                Collection::make([1, 2, 3]),
                Collection::make([1, 2, 3])
            ],
            [
                Collection::make([
                    [1, 2, 3],
                    [2, 3, 4],
                    5
                ]),
                Collection::make([1, 2, 3, 2, 3, 4, 5])
            ],
            [
                Collection::make([
                    'element',
                    Collection::make(['one', 'two']),
                    'another',
                    1,
                    [1, 2, 'hi'],
                    'the end',
                ]),
                Collection::make(['element', 'one', 'two', 'another', 1, 1, 2, 'hi', 'the end'])
            ],
        ];
    }
}
