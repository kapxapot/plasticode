<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Collection;

final class CollectionTest extends TestCase
{
    /** @dataProvider flattenProvider */
    public function testFlatten(Collection $original, Collection $expected) : void
    {
        $actual = $original->flatten();

        $this->assertEquals($expected->toArray(), $actual->toArray());
    }

    public function flattenProvider()
    {
        return [
            [
                Collection::make([1, 2, 3]),
                Collection::make([1, 2, 3])
            ],
            [
                Collection::make(
                    [
                        [1, 2, 3],
                        [2, 3, 4],
                        5
                    ]
                ),
                Collection::make([1, 2, 3, 2, 3, 4, 5])
            ],
            [
                Collection::make(
                    [
                        'element',
                        Collection::make(['one', 'two']),
                        'another',
                        1,
                        [1, 2, 'hi'],
                        'the end',
                    ]
                ),
                Collection::make(
                    [
                        'element',
                        'one',
                        'two',
                        'another',
                        1,
                        1,
                        2,
                        'hi',
                        'the end'
                    ]
                )
            ],
        ];
    }

    /** @dataProvider jsonEncodeProvider */
    public function testJsonEncode(Collection $original, array $expected) : void
    {
        $actual = json_encode($original);

        $this->assertEquals(json_encode($expected), $actual);
    }

    public function jsonEncodeProvider()
    {
        return [
            [
                Collection::makeEmpty(),
                []
            ],
            [
                Collection::make([1, 2, 3]),
                [1, 2, 3]
            ],
            [
                Collection::make(
                    [
                        [1, 2, 3],
                        [2, 3, 4],
                        5
                    ]
                ),
                [
                    [1, 2, 3],
                    [2, 3, 4],
                    5
                ]
            ],
            [
                Collection::make(
                    [
                        'element',
                        Collection::make(['one', 'two']),
                        'another',
                        1,
                        [1, 2, 'hi'],
                        'the end',
                    ]
                ),
                [
                    'element',
                    ['one', 'two'],
                    'another',
                    1,
                    [1, 2, 'hi'],
                    'the end',
                ]
            ],
        ];
    }
}
