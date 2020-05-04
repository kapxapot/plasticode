<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Testing\Dummies\DummyCollection;
use Plasticode\Testing\Dummies\DummyModel;

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
        $col = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $flat = $col->flatten();

        $this->assertEquals(Collection::class, get_class($flat));
    }

    public function testConcatPreservesCollectionType() : void
    {
        $col = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $concat = $col->concat(
            DummyCollection::make(
                [
                    new DummyModel(3, 'three'),
                ]
            )
        );

        $this->assertEquals(DummyCollection::class, get_class($concat));
        $this->assertCount(3, $concat);
    }

    public function testConcatBaseCollectionAllowsHeteroTypes() : void
    {
        $col = Collection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
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

        $col = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
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
        $col = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $concat = $col->add(
            new DummyModel(3, 'three'),
        );

        $this->assertEquals(DummyCollection::class, get_class($concat));
        $this->assertCount(3, $concat);
    }

    public function testAddBaseCollectionAllowsHeteroTypes() : void
    {
        $col = Collection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $concat = $col->add(1)->add(2)->add(3);

        $this->assertEquals(Collection::class, get_class($concat));
        $this->assertCount(5, $concat);
    }

    public function testAddTypedCollectionDoesntAllowHeteroTypes() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $col = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $col->add(1);
    }

    public function testMergeReturnsRightCollection() : void
    {
        $col1 = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
            ]
        );

        $col2 = DummyCollection::make(
            [
                new DummyModel(3, 'three'),
            ]
        );

        $col = DummyCollection::merge($col1, $col2);

        $this->assertEquals(DummyCollection::class, get_class($col));
        $this->assertCount(3, $col);
    }

    public function testMergeAllowsHeteroTypes() : void
    {
        $col1 = DummyCollection::make(
            [
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
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
                new DummyModel(1, 'one'),
                new DummyModel(2, 'two'),
                new DummyModel(3, 'three'),
            ]
        );

        /** @var DummyModel */
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
}
