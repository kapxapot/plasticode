<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Util\Arrays;

final class GroupByTest extends TestCase
{
    /**
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

        $dummy1 = new ModelDummy(1, 'one');
        $dummy11 = new ModelDummy(1, 'one one');
        $dummy2 = new ModelDummy(2, 'one');

        $testObjArray = [$dummy1, $dummy11, $dummy2];

        return [
            'empty' => [[], 'a', []],
            'array_property' => [
                $testArray,
                'name',
                [
                    'one' => [$item1, $item2],
                    'one one' => [$item11],
                ]
            ],
            'obj_property' => [
                $testObjArray,
                'name',
                [
                    'one' => [$dummy1, $dummy2],
                    'one one' => [$dummy11],
                ]
            ],
            'array_callable' => [
                $testArray,
                fn (array $item) => $item['id'] . $item['name'],
                [
                    '1one' => [$item1],
                    '1one one' => [$item11],
                    '2one' => [$item2],
                ]
            ],
            'obj_callable' => [
                $testObjArray,
                fn (ModelDummy $item) => $item->id . $item->name,
                [
                    '1one' => [$dummy1],
                    '1one one' => [$dummy11],
                    '2one' => [$dummy2],
                ]
            ],
            'mixed_property' => [
                [...$testArray, 1, 'one', 'two', null],
                'name',
                [
                    'one' => [$item1, $item2, 'one'],
                    'one one' => [$item11],
                    1 => [1],
                    'two' => ['two'],
                ]
            ],
        ];
    }
}
