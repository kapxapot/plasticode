<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\DummyModel;
use Plasticode\Util\Arrays;

final class LastByTest extends TestCase
{
    /**
     * @covers Arrays
     * @dataProvider lastByClosureProvider
     *
     * @param array $array
     * @param \Closure $by
     * @param mixed $result
     * @return void
     */
    public function testLastByClosure(array $array, \Closure $by, $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::lastBy($array, $by)
        );
    }

    public function lastByClosureProvider()
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
                $item2
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
                    return $item['name'] == 'one';
                },
                $item1
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
                $dummy2
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
                    return $obj->name == 'one';
                },
                $dummy1
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
    public function testLastByClosureIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::lastBy(
            [],
            function ($item) {
                return true;
            },
            'some value'
        );
    }

    /**
     * @covers Arrays
     * @dataProvider lastByPropertyProvider
     *
     * @param array $array
     * @param string $by
     * @param mixed $value
     * @param mixed $result
     * @return void
     */
    public function testLastByProperty(array $array, string $by, $value, $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::lastBy($array, $by, $value)
        );
    }

    public function lastByPropertyProvider()
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
    public function testLastByPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::lastBy([], 'name');
    }
}
