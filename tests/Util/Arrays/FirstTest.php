<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\Dummies\DummyModel;
use Plasticode\Util\Arrays;

final class FirstTest extends TestCase
{
    /**
     * @dataProvider firstProvider
     *
     * @param array $array
     * @param mixed $result
     * @return void
     */
    public function testFirst(array $array, $result) : void
    {
        $this->assertEquals($result, Arrays::first($array));
    }

    public function firstProvider() : array
    {
        return [
            [[], null],
            [['a', 'b'], 'a'],
        ];
    }

    /**
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

    public function firstByClosureProvider() : array
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

    public function firstByPropertyProvider() : array
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

    public function testFirstByPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::firstBy([], 'name');
    }
}
