<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\DummyModel;
use Plasticode\Util\Arrays;

final class ExtractTest extends TestCase
{
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
}
