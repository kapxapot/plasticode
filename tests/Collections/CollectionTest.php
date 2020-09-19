<?php

namespace Plasticode\Tests\Collections;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Testing\Dummies\CollectionDummy;
use Plasticode\Testing\Dummies\ModelDummy;

final class CollectionTest extends TestCase
{
    /**
     * @dataProvider flattenProvider
     */
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

    public function testFlattenCreatesBaseCollection() : void
    {
        $col = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $flat = $col->flatten();

        $this->assertEquals(Collection::class, get_class($flat));
    }

    public function testConcatPreservesCollectionType() : void
    {
        $col = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $concat = $col->concat(
            CollectionDummy::make(
                [
                    new ModelDummy(3, 'three'),
                ]
            )
        );

        $this->assertEquals(CollectionDummy::class, get_class($concat));
        $this->assertCount(3, $concat);
    }

    public function testConcatBaseCollectionAllowsHeteroTypes() : void
    {
        $col = Collection::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $concat = $col->concat(
            Collection::make(
                [1, 2, 3]
            )
        );

        $this->assertEquals(Collection::class, get_class($concat));
        $this->assertCount(5, $concat);
    }

    public function testConcatTypedCollectionDoesntAllowHeteroTypes() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $col = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $col->concat(
            Collection::make(
                [1, 2, 3]
            )
        );
    }

    public function testAddPreservesCollectionType() : void
    {
        $col = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $concat = $col->add(
            new ModelDummy(3, 'three'),
        );

        $this->assertEquals(CollectionDummy::class, get_class($concat));
        $this->assertCount(3, $concat);
    }

    public function testAddBaseCollectionAllowsHeteroTypes() : void
    {
        $col = Collection::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $concat = $col->add(1)->add(2)->add(3);

        $this->assertEquals(Collection::class, get_class($concat));
        $this->assertCount(5, $concat);
    }

    public function testAddTypedCollectionDoesntAllowHeteroTypes() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $col = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $col->add(1);
    }

    public function testMergeReturnsRightCollection() : void
    {
        $col1 = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $col2 = CollectionDummy::make(
            [
                new ModelDummy(3, 'three'),
            ]
        );

        $col = CollectionDummy::merge($col1, $col2);

        $this->assertEquals(CollectionDummy::class, get_class($col));
        $this->assertCount(3, $col);
    }

    public function testMergeAllowsHeteroTypes() : void
    {
        $col1 = CollectionDummy::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
            ]
        );

        $col2 = Collection::make(
            [1, 2, 3]
        );

        $col = Collection::merge($col1, $col2);

        $this->assertEquals(Collection::class, get_class($col));
        $this->assertCount(5, $col);
    }

    /**
     * @dataProvider jsonEncodeProvider
     */
    public function testJsonEncode(Collection $original, array $expected) : void
    {
        $actual = json_encode($original);

        $this->assertEquals(json_encode($expected), $actual);
    }

    public function jsonEncodeProvider()
    {
        return [
            [
                Collection::empty(),
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

    public function testLastObjRemembers() : void
    {
        $col = Collection::make(
            [
                new ModelDummy(1, 'one'),
                new ModelDummy(2, 'two'),
                new ModelDummy(3, 'three'),
            ]
        );

        /** @var ModelDummy */
        $last = $col->last();

        $last->name = 'four';

        $this->assertEquals('four', $col->last()->name);
    }

    public function testLastArrayForgets() : void
    {
        $col = Collection::make(
            [
                ['id' => 1, 'name' => 'one'],
                ['id' => 2, 'name' => 'two'],
                ['id' => 3, 'name' => 'three'],
            ]
        );

        /** @var array */
        $last = $col->last();

        $last['name'] = 'four';

        $this->assertEquals('three', $col->last()['name']);
    }

    /**
     * @dataProvider cleanProvider
     */
    public function testClean(Collection $col, Collection $result) : void
    {
        $this->assertEquals(
            $result->toArray(),
            $col->clean()->toArray()
        );
    }

    public function cleanProvider() : array
    {
        return [
            [
                Collection::empty(),
                Collection::empty()
            ],
            [
                Collection::make(
                    [
                        'some',
                        '',
                        'string',
                        null,
                        [1, 2, 3],
                        0
                    ]
                ),
                Collection::make(
                    ['some', 'string', [1, 2, 3]]
                )
            ],
            [
                Collection::make(['already', 'clean']),
                Collection::make(['already', 'clean'])
            ],
        ];
    }

    public function testShuffle() : void
    {
        $original = Collection::make([1, 2, 3]);
        $shuffled = $original->shuffle();

        $this->assertNotSame($shuffled, $original);
        $this->assertSameSize($shuffled, $original);
        $this->assertEqualsCanonicalizing($shuffled->toArray(), $original->toArray());
    }


    /**
     * @dataProvider removeFirstProvider
     */
    public function testRemoveFirst(
        Collection $original,
        Collection $expected,
        callable $by
    ) : void
    {
        $this->assertEquals(
            $expected->toArray(),
            $original->removeFirst($by)->toArray()
        );
    }

    public function removeFirstProvider() : array
    {
        $dummy1 = new ModelDummy(1, 'one');
        $dummy2 = new ModelDummy(2, 'two');
        $dummy3 = new ModelDummy(3, 'three');

        $col = Collection::make([$dummy1, $dummy2, $dummy3, $dummy2, $dummy3]);

        return [
            [
                $col,
                Collection::make([$dummy2, $dummy3, $dummy2, $dummy3]),
                fn (ModelDummy $d) => $d->id == 1
            ],
            [
                $col,
                Collection::make([$dummy1, $dummy3, $dummy2, $dummy3]),
                fn (ModelDummy $d) => $d->id == 2
            ],
            [
                $col,
                Collection::make([$dummy1, $dummy2, $dummy2, $dummy3]),
                fn (ModelDummy $d) => $d->id == 3
            ],
            [
                $col,
                Collection::make([$dummy1, $dummy2, $dummy3, $dummy2, $dummy3]),
                fn (ModelDummy $d) => $d->id == 4
            ]
        ];
    }

    /**
     * @dataProvider tailProvider
     */
    public function testTail(
        Collection $original,
        int $limit,
        Collection $expected
    ) : void
    {
        $this->assertEquals(
            $expected->toArray(),
            $original->tail($limit)->toArray()
        );
    }

    public function tailProvider() : array
    {
        $empty = Collection::empty();
        $col = Collection::make(['one', 'two', 'three']);

        return [
            [$empty, 1, $empty],
            [$empty, 3, $empty],
            [$empty, 5, $empty],
            [$col, 1, Collection::make(['three'])],
            [$col, 2, Collection::make(['two', 'three'])],
            [$col, 3, $col],
            [$col, 5, $col],
        ];
    }

    /**
     * @dataProvider joinProvider
     */
    public function testJoin(Collection $original, string $delimiter, string $expected) : void
    {
        $this->assertEquals(
            $expected,
            $original->join($delimiter)
        );
    }

    public function joinProvider() : array
    {
        return [
            [
                Collection::empty(),
                '',
                ''
            ],
            [
                Collection::empty(),
                ',',
                ''
            ],
            [
                Collection::make([1, 2]),
                '',
                '12'
            ],
            [
                Collection::make([1, 2]),
                ',',
                '1,2'
            ],
            [
                Collection::make(['a', 'b', 'c']),
                '',
                'abc'
            ],
            [
                Collection::make(['a', 'b', 'c']),
                '-',
                'a-b-c'
            ],
        ];
    }
}
