<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Util\Arrays;

final class FirstTest extends TestCase
{
    /**
     * @dataProvider firstProvider
     *
     * @param mixed $result
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
     * @param mixed $result
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
                function (ModelDummy $obj) {
                    return strlen($obj->name) == 3;
                },
                $dummy1
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
                    return $obj->name == 'two';
                },
                $dummy2
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

    public function testFirstByClosureIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::firstBy(
            [1],
            function ($item) {
                return true;
            },
            'some value'
        );
    }

    /**
     * @dataProvider firstByPropertyProvider
     *
     * @param mixed $value
     * @param mixed $result
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
        $item1 = ['id' => 1, 'name' => 'one', 'type' => null];
        $item2 = ['id' => 2, 'name' => 'two', 'type' => 'one'];
        $item3 = ['id' => 3, 'name' => 'three', 'type' => 'two'];

        $testArray = [$item1, $item2, $item3];

        $dummy1 = new ModelDummy(1, 'one');
        $dummy2 = (new ModelDummy(2, 'two'))->withType('one');
        $dummy3 = new ModelDummy(3, 'three');

        $testObjArray = [$dummy1, $dummy2, $dummy3];

        // array, by, value, result

        return [
            [$testArray, 'name', 'two', $item2],
            [$testArray, 'age', 3, null],
            [$testArray, 'name', 'four', null],
            [$testArray, 'type', null, $item1],
            [$testObjArray, 'name', 'two', $dummy2],
            [$testObjArray, 'age', 3, null],
            [$testObjArray, 'name', 'four', null],
        ];
    }

    public function testFirstByPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::firstBy([1], 'name');
    }
}
