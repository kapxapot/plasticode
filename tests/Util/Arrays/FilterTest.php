<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Tests\DummyModel;
use Plasticode\Util\Arrays;

final class FilterTest extends TestCase
{
    /**
     * @covers Arrays
     * @dataProvider filterClosureProvider
     *
     * @param array $array
     * @param \Closure $by
     * @param array $result
     * @return void
     */
    public function testFilterClosure(array $array, \Closure $by, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::filter($array, $by)
        );
    }

    public function filterClosureProvider() : array
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
                [$item1, $item2]
            ],
            [
                $testArray,
                function (array $item) {
                    return strlen($item['name']) == 5;
                },
                [$item3]
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['name'] == 'two';
                },
                [$item2]
            ],
            [
                $testArray,
                function (array $item) {
                    return $item['name'] == 'four';
                },
                []
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return strlen($obj->name) == 3;
                },
                [$dummy1, $dummy2]
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return strlen($obj->name) == 5;
                },
                [$dummy3]
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return $obj->name == 'two';
                },
                [$dummy2]
            ],
            [
                $testObjArray,
                function (DummyModel $obj) {
                    return $obj->name == 'four';
                },
                []
            ],
        ];
    }

    /**
     * @covers Arrays
     * 
     * @return void
     */
    public function testFilterClosureIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::filter(
            [],
            function ($item) {
                return true;
            },
            'some value'
        );
    }

    /**
     * @covers Arrays
     * @dataProvider filterPropertyProvider
     *
     * @param array $array
     * @param string $by
     * @param mixed $value
     * @param array $result
     * @return void
     */
    public function testFilterProperty(array $array, string $by, $value, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::filter($array, $by, $value)
        );
    }

    public function filterPropertyProvider() : array
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
            [$testArray, 'name', 'two', [$item2]],
            [$testArray, 'age', 3, []],
            [$testArray, 'name', 'four', []],
            [$testObjArray, 'name', 'two', [$dummy2]],
            [$testObjArray, 'age', 3, []],
            [$testObjArray, 'name', 'four', []],
        ];
    }

    /**
     * @covers Arrays
     * 
     * @return void
     */
    public function testFilterPropertyIncorrectParams() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::filter([], 'name');
    }

    /**
     * @covers Arrays
     * @dataProvider filterInProvider
     *
     * @param array $array
     * @param string $column
     * @param mixed $values
     * @param array $result
     * @return void
     */
    public function testFilterIn(array $array, string $column, array $values, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::filterIn($array, $column, $values)
        );
    }

    public function filterInProvider() : array
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
            [$testArray, 'name', ['one', 'two'], [$item1, $item2]],
            [$testArray, 'name', ['three'], [$item3]],
            [$testArray, 'age', [3], []],
            [$testArray, 'name', ['four'], []],
            [$testObjArray, 'name', ['one', 'two'], [$dummy1, $dummy2]],
            [$testObjArray, 'name', ['three'], [$dummy3]],
            [$testObjArray, 'age', [3], []],
            [$testObjArray, 'name', ['four'], []],
        ];
    }

    /**
     * @covers Arrays
     * @dataProvider filterNotInProvider
     *
     * @param array $array
     * @param string $column
     * @param mixed $values
     * @param array $result
     * @return void
     */
    public function testFilterNotIn(array $array, string $column, array $values, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::filterNotIn($array, $column, $values)
        );
    }

    public function filterNotInProvider() : array
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
            [$testArray, 'name', ['one', 'two'], [$item3]],
            [$testArray, 'name', ['three'], [$item1, $item2]],
            [$testArray, 'age', [3], $testArray],
            [$testArray, 'name', ['four'], $testArray],
            [$testObjArray, 'name', ['one', 'two'], [$dummy3]],
            [$testObjArray, 'name', ['three'], [$dummy1, $dummy2]],
            [$testObjArray, 'age', [3], $testObjArray],
            [$testObjArray, 'name', ['four'], $testObjArray],
        ];
    }
}
