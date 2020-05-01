<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Util\Arrays;

final class DistinctByTest extends TestCase
{
    /**
     * @dataProvider distinctByProvider
     *
     * @param array $array
     * @param string|callable|null $by
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
            'empty' => [[], 'a', []],
            'array_property' => [
                $testArray,
                'name',
                [$item1, $item11]
            ],
            'obj_property' => [
                $testObjArray,
                'name',
                [$dummy1, $dummy11]
            ],
            'array_callable' => [
                $testArray,
                fn (array $item) => $item['id'] . $item['name'],
                $testArray
            ],
            'obj_callable' => [
                $testObjArray,
                fn (DummyModel $item) => $item->id . $item->name,
                $testObjArray
            ],
            'mixed_property' => [
                [...$testArray, 1, 'one', 'two', null],
                'name',
                [$item1, $item11, 1, 'two']
            ],
            'scalar_null' => [
                [1, 2, 3, 2, 1, 'one', 'two', null, 'two'],
                null,
                [1, 2, 3, 'one', 'two']
            ],
        ];
    }
}
