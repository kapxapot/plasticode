<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;

final class ArraysTest extends TestCase
{
    /** @var array */
    const ABC = ['a', 'b', 'c'];

    /** @var array */
    const AvBvCv = [
        'a' => 'av',
        'b' => 'bv',
        'c' => 'cv'
    ];

    /** @var array */
    const A_Bc = [
        'a' => ['b' => 'c']
    ];

    /**
     * @covers Arrays
     * @dataProvider existsProvider
     * 
     * @param array $array
     * @param string|integer|null $key
     * @param boolean $result
     * @return void
     */
    public function testExists(array $array, $key, bool $result) : void
    {
        $this->assertEquals($result, Arrays::exists($array, $key));
    }

    public function existsProvider() : array
    {
        return [
            [[], null, false],
            [[], 'a', false],
            [self::ABC, null, false],
            [self::ABC, 'a', false],
            [self::ABC, 1, true],
            [self::ABC, 5, false],
            [self::AvBvCv, null, false],
            [self::AvBvCv, 'b', true],
            [self::AvBvCv, 'd', false],
            [self::AvBvCv, 1, false],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider getProvider
     *
     * @param array $array
     * @param string|integer|null $key
     * @param mixed $result
     * @return void
     */
    public function testGet(array $array, $key, $result) : void
    {
        $this->assertEquals($result, Arrays::get($array, $key));
    }

    public function getProvider() : array
    {
        return [
            [[], null, null],
            [[], 'a', null],
            [self::ABC, null, null],
            [self::ABC, 'a', null],
            [self::ABC, 1, 'b'],
            [self::ABC, 5, null],
            [self::AvBvCv, null, null],
            [self::AvBvCv, 'b', 'bv'],
            [self::AvBvCv, 'd', null],
            [self::A_Bc, 'a.b', 'c'],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider setProvider
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function testSet(array $array, $key, $value) : void
    {
        Arrays::set($array, $key, $value);

        $this->assertEquals($value, Arrays::get($array, $key));
    }

    public function setProvider() : array
    {
        $v = 'v';

        return [
            [[], 'a', $v],
            [self::ABC, 'a', $v],
            [self::ABC, 1, $v],
            [self::AvBvCv, 'b', $v],
            [self::AvBvCv, 'd', $v],
            [self::A_Bc, 'a.b', $v],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider setNullKeyProvider
     *
     * @param array $array
     * @param mixed $value
     * @return void
     */
    public function testSetNullKey(array $array, $value) : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::set($array, null, $value);
    }

    public function setNullKeyProvider() : array
    {
        $v = 'v';

        return [
            [[], $v],
            [self::ABC, $v],
            [self::AvBvCv, $v],
            [self::A_Bc, $v],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider distinctByIdProvider
     *
     * @param array $array
     * @param array $result
     * @return void
     */
    public function testDistinctById(array $array, array $result) : void
    {
        $this->assertEquals($result, Arrays::distinctById($array));
    }

    public function distinctByIdProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'two'];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'two');

        return [
            [[], []],
            [
                [$item1, $item11, $item2],
                [$item1, $item2]
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                [$dummy1, $dummy2]
            ]
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider distinctByProvider
     *
     * @param array $array
     * @param string|\Closure $by
     * @param array $result
     * @return void
     */
    public function testDistinctBy(array $array, $by, array $result) : void
    {
        $this->assertEquals($result, Arrays::distinctBy($array, $by));
    }

    public function distinctByProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'one'];
        
        $testArray = [$item1, $item11, $item2];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'one');

        $testObjArray = [$dummy1, $dummy11, $dummy2];

        return [
            [[], 'a', []],
            [
                $testArray,
                'name',
                [$item1, $item11]
            ],
            [
                $testObjArray,
                'name',
                [$dummy1, $dummy11]
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['id'] . $item['name'];
                },
                $testArray
            ],
            [
                $testObjArray,
                function (DummyModel $item) {
                    return $item->id . $item->name;
                },
                $testObjArray
            ],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider toAssocByIdProvider
     * 
     * @param array $array
     * @param array $result
     * @return void
     */
    public function testToAssocById(array $array, array $result) : void
    {
        $this->assertEquals($result, Arrays::toAssocById($array));
    }

    public function toAssocByIdProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'two'];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'two');

        return [
            [[], []],
            [
                [$item1, $item11, $item2],
                [
                    1 => $item1,
                    2 => $item2,
                ]
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                [
                    1 => $dummy1,
                    2 => $dummy2,
                ]
            ]
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider toAssocByProvider
     *
     * @param array $array
     * @param string|\Closure $by
     * @param array $result
     * @return void
     */
    public function testToAssocBy(array $array, $by, array $result) : void
    {
        $this->assertEquals($result, Arrays::toAssocBy($array, $by));
    }

    public function toAssocByProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'one'];

        $testArray = [$item1, $item11, $item2];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'one');

        $testObjArray = [$dummy1, $dummy11, $dummy2];

        return [
            [[], 'a', []],
            [
                $testArray,
                'name',
                [
                    'one' => $item1,
                    'one one' => $item11,
                ]
            ],
            [
                $testObjArray,
                'name',
                [
                    'one' => $dummy1,
                    'one one' => $dummy11,
                ]
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['id'] . $item['name'];
                },
                [
                    '1one' => $item1,
                    '1one one' => $item11,
                    '2one' => $item2,
                ]
            ],
            [
                $testObjArray,
                function (DummyModel $item) {
                    return $item->id . $item->name;
                },
                [
                    '1one' => $dummy1,
                    '1one one' => $dummy11,
                    '2one' => $dummy2,
                ]
            ],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider groupByIdProvider
     * 
     * @param array $array
     * @param array $result
     * @return void
     */
    public function testGroupById(array $array, array $result) : void
    {
        $this->assertEquals($result, Arrays::groupById($array));
    }

    public function groupByIdProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'two'];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'two');

        return [
            [[], []],
            [
                [$item1, $item11, $item2],
                [
                    1 => [$item1, $item11],
                    2 => [$item2],
                ]
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                [
                    1 => [$dummy1, $dummy11],
                    2 => [$dummy2],
                ]
            ]
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider groupByProvider
     *
     * @param array $array
     * @param string|\Closure $by
     * @param array $result
     * @return void
     */
    public function testGroupBy(array $array, $by, array $result) : void
    {
        $this->assertEquals($result, Arrays::groupBy($array, $by));
    }

    public function groupByProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'one'];

        $testArray = [$item1, $item11, $item2];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'one');

        $testObjArray = [$dummy1, $dummy11, $dummy2];

        return [
            [[], 'a', []],
            [
                $testArray,
                'name',
                [
                    'one' => [$item1, $item2],
                    'one one' => [$item11],
                ]
            ],
            [
                $testObjArray,
                'name',
                [
                    'one' => [$dummy1, $dummy2],
                    'one one' => [$dummy11],
                ]
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['id'] . $item['name'];
                },
                [
                    '1one' => [$item1],
                    '1one one' => [$item11],
                    '2one' => [$item2],
                ]
            ],
            [
                $testObjArray,
                function (DummyModel $item) {
                    return $item->id . $item->name;
                },
                [
                    '1one' => [$dummy1],
                    '1one one' => [$dummy11],
                    '2one' => [$dummy2],
                ]
            ],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider extractIdsProvider
     *
     * @param array $array
     * @param array $result
     * @return void
     */
    public function testExtractIds(array $array, array $result) : void
    {
        $this->assertEquals($result, Arrays::extractIds($array));
    }

    public function extractIdsProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'two'];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'two');

        return [
            [[], []],
            [
                [$item1, $item11, $item2],
                [1, 2]
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                [1, 2]
            ]
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider extractProvider
     *
     * @param array $array
     * @param string $column
     * @param array $result
     * @return void
     */
    public function testExtract(array $array, string $column, array $result) : void
    {
        $this->assertEquals($result, Arrays::extract($array, $column));
    }

    public function extractProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'one'];

        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'one');

        return [
            [[], 'a', []],
            [
                [$item1, $item11, $item2],
                'name',
                ['one', 'one one']
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                'name',
                ['one', 'one one']
            ]
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider firstByClosureProvider
     *
     * @param array $array
     * @param \Closure $by
     * @param mixed $result
     * @return void
     */
    public function testFirstByClosure(array $array, \Closure $by, $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::firstBy($array, $by)
        );
    }

    public function firstByClosureProvider()
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item2 = ['id' => 2, 'name' => 'two'];
        $item3 = ['id' => 3, 'name' => 'three'];

        $testArray = [$item1, $item2, $item3];

        $dummy1 = new DummyModel(1, 'one');
        $dummy2 = new DummyModel(2, 'two');
        $dummy3 = new DummyModel(3, 'three');

        $testObjArray = [$dummy1, $dummy2, $dummy3];

        return [
            [
                $testArray,
                function (array $item) {
                    return strlen($item['name']) == 3;
                },
                $item1
            ],
            [
                $testArray,
                function (array $item) {
                    return strlen($item['name']) == 5;
                },
                $item3
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['name'] == 'two';
                },
                $item2
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['name'] == 'four';
                },
                null
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return strlen($obj->name) == 3;
                },
                $dummy1
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return strlen($obj->name) == 5;
                },
                $dummy3
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return $obj->name == 'two';
                },
                $dummy2
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return $obj->name == 'four';
                },
                null
            ],
        ];
    }

    /**
     * @covers Arrays
     * 
     * @return void
     */
    public function testFirstByClosureIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::firstBy(
            [],
            function ($item) {
                return true;
            },
            'some value'
        );
    }

    /**
     * @covers Arrays
     * @dataProvider firstByPropertyProvider
     *
     * @param array $array
     * @param string $by
     * @param mixed $value
     * @param mixed $result
     * @return void
     */
    public function testFirstByProperty(array $array, string $by, $value, $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::firstBy($array, $by, $value)
        );
    }

    public function firstByPropertyProvider()
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item2 = ['id' => 2, 'name' => 'two'];
        $item3 = ['id' => 3, 'name' => 'three'];

        $testArray = [$item1, $item2, $item3];

        $dummy1 = new DummyModel(1, 'one');
        $dummy2 = new DummyModel(2, 'two');
        $dummy3 = new DummyModel(3, 'three');

        $testObjArray = [$dummy1, $dummy2, $dummy3];

        return [
            [$testArray, 'name', 'two', $item2],
            [$testArray, 'age', 3, null],
            [$testArray, 'name', 'four', null],
            [$testObjArray, 'name', 'two', $dummy2],
            [$testObjArray, 'age', 3, null],
            [$testObjArray, 'name', 'four', null],
        ];
    }

    /**
     * @covers Arrays
     * 
     * @return void
     */
    public function testFirstByPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::firstBy([], 'name');
    }
}
