<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;

final class ArraysTest extends TestCase
{
    const ABC = ['a', 'b', 'c'];

    const AvBvCv = [
        'a' => 'av',
        'b' => 'bv',
        'c' => 'cv'
    ];

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
     */
    public function testExists(array $array, $key, bool $result)
    {
        $this->assertEquals($result, Arrays::exists($array, $key));
    }

    public function existsProvider()
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
     */
    public function testGet(array $array, $key, $result)
    {
        $this->assertEquals($result, Arrays::get($array, $key));
    }

    public function getProvider()
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
     */
    public function testSet(array $array, $key, $value)
    {
        Arrays::set($array, $key, $value);

        $this->assertEquals($value, Arrays::get($array, $key));
    }

    public function setProvider()
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
     */
    public function testSetNullKey(array $array, $value)
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::set($array, null, $value);
    }

    public function setNullKeyProvider()
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
    public function testDistinctById(array $array, array $result)
    {
        $this->assertEquals($result, Arrays::distinctById($array));
    }

    public function distinctByIdProvider()
    {
        $dummy1 = new DummyModel(1, 'one');
        $dummy11 = new DummyModel(1, 'one one');
        $dummy2 = new DummyModel(2, 'two');

        return [
            [[], []],
            [
                [
                    ['id' => 1, 'name' => 'one'],
                    ['id' => 1, 'name' => 'one one'],
                    ['id' => 2, 'name' => 'two'],
                ],
                [
                    ['id' => 1, 'name' => 'one'],
                    ['id' => 2, 'name' => 'two'],
                ]
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
    public function testDistinctBy(array $array, $by, array $result)
    {
        $this->assertEquals($result, Arrays::distinctBy($array, $by));
    }

    public function distinctByProvider()
    {
        $testArray = [
            ['id' => 1, 'name' => 'one'],
            ['id' => 1, 'name' => 'one one'],
            ['id' => 2, 'name' => 'one'],
        ];

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
                    ['id' => 1, 'name' => 'one'],
                    ['id' => 1, 'name' => 'one one'],
                ]
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
}
