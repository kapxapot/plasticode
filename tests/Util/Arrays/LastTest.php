<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Util\Arrays;

final class LastTest extends TestCase
{
    /**
     * @dataProvider lastProvider
     *
     * @param array $array
     * @param mixed $result
     * @return void
     */
    public function testLast(array $array, $result) : void
    {
        $this->assertEquals($result, Arrays::last($array));
    }

    public function lastProvider() : array
    {
        return [
            [[], null],
            [['a', 'b'], 'b'],
        ];
    }

    /**
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

    public function lastByClosureProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item2 = ['id' => 2, 'name' => 'two'];
        $item3 = ['id' => 3, 'name' => 'three'];

        $testArray = [$item1, $item2, $item3];

        $dummy1 = new ModelDummy(1, 'one');
        $dummy2 = new ModelDummy(2, 'two');
        $dummy3 = new ModelDummy(3, 'three');

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
                function (ModelDummy $obj) {
                    return strlen($obj->name) == 3;
                },
                $dummy2
            ],
            [
                $testObjArray,
                function (ModelDummy $obj) {
                    return strlen($obj->name) == 5;
                },
                $dummy3
            ],
            [
                $testObjArray,
                function (ModelDummy $obj) {
                    return $obj->name == 'one';
                },
                $dummy1
            ],
            [
                $testObjArray,
                function (ModelDummy $obj) {
                    return $obj->name == 'four';
                },
                null
            ],
        ];
    }

    public function testLastByClosureIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::lastBy(
            [1],
            function ($item) {
                return true;
            },
            'some value'
        );
    }

    /**
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

    public function lastByPropertyProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item2 = ['id' => 2, 'name' => 'two'];
        $item3 = ['id' => 3, 'name' => 'three'];

        $testArray = [$item1, $item2, $item3];

        $dummy1 = new ModelDummy(1, 'one');
        $dummy2 = new ModelDummy(2, 'two');
        $dummy3 = new ModelDummy(3, 'three');

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

    public function testLastByPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::lastBy([1], 'name');
    }
}
