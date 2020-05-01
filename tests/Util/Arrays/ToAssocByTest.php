<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Util\Arrays;

final class ToAssocByTest extends TestCase
{
    /**
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
            'empty' => [[], 'a', []],
            'array_property' => [
                $testArray,
                'name',
                [
                    'one' => $item1,
                    'one one' => $item11,
                ]
            ],
            'obj_property' => [
                $testObjArray,
                'name',
                [
                    'one' => $dummy1,
                    'one one' => $dummy11,
                ]
            ],
            'array_callable' => [
                $testArray,
                fn (array $item) => $item['id'] . $item['name'],
                [
                    '1one' => $item1,
                    '1one one' => $item11,
                    '2one' => $item2,
                ]
            ],
            'obj_callable' => [
                $testObjArray,
                fn (DummyModel $item) => $item->id . $item->name,
                [
                    '1one' => $dummy1,
                    '1one one' => $dummy11,
                    '2one' => $dummy2,
                ]
            ],
            'mixed_property' => [
                [...$testArray, 1, 'one', 'two', null],
                'name',
                [
                    'one' => $item1,
                    'one one' => $item11,
                    1 => 1,
                    'two' => 'two',
                ]
            ],
        ];
    }
}
