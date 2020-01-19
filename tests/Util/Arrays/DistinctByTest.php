<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\DummyModel;
use Plasticode\Util\Arrays;

final class DistinctByTest extends TestCase
{
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
}
