<?php

namespace Plasticode\Tests\Util;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\SortDummy;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

final class SortTest extends TestCase
{
    private $first = [
        'num' => 1,
        'str' => 'one',
        'bool' => true,
        'null' => null,
        'date' => '2020-01-10',
    ];

    private $second = [
        'num' => 2,
        'str' => 'two',
        'bool' => false,
        'null' => 'null',
        'date' => '2019-10-10',
    ];

    private $third = [
        'num' => 3,
        'str' => 'three',
        'bool' => false,
        'null' => null,
        'date' => '2018-06-07',
    ];

    /**
     * @dataProvider byManyProvider
     * 
     * @param SortStep[] $steps
     */
    public function testByMany(array $array, array $steps, array $expected) : void
    {
        $actual = Sort::byMany($array, ...$steps);

        $this->assertEquals($expected, $actual);
    }

    public function byManyProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        return [
            [[], [], []],
            [$array, [], $array],
            [
                $array,
                [
                    SortStep::asc('num'),
                ],
                $array
            ],
            [
                $array,
                [
                    SortStep::desc('num'),
                ],
                [$this->third, $this->second, $this->first]
            ],
            [
                $array,
                [
                    SortStep::asc('str', Sort::STRING),
                ],
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                [
                    SortStep::desc('str', Sort::STRING),
                ],
                [$this->second, $this->third, $this->first]
            ],
            [
                $array,
                [
                    SortStep::asc('bool', Sort::BOOL),
                    SortStep::asc('num'),
                ],
                [$this->second, $this->third, $this->first]
            ],
            [
                $array,
                [
                    SortStep::desc('bool', Sort::BOOL),
                    SortStep::asc('num'),
                ],
                [$this->first, $this->second, $this->third]
            ],
            [
                $array,
                [
                    SortStep::asc('null', Sort::NULL),
                    SortStep::asc('num'),
                ],
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                [
                    SortStep::desc('null', Sort::NULL),
                    SortStep::asc('num'),
                ],
                [$this->second, $this->first, $this->third]
            ],
            [
                $array,
                [
                    SortStep::asc('date', Sort::DATE),
                ],
                [$this->third, $this->second, $this->first]
            ],
            [
                $array,
                [
                    SortStep::desc('date', Sort::DATE),
                ],
                [$this->first, $this->second, $this->third]
            ],
            [$arrayObj, [], $arrayObj],
            [
                $arrayObj,
                [
                    SortStep::asc('num'),
                ],
                $arrayObj
            ],
            [
                $arrayObj,
                [
                    SortStep::desc('num'),
                ],
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::asc('str', Sort::STRING),
                ],
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
            [
                $arrayObj,
                [
                    SortStep::desc('str', Sort::STRING),
                ],
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::asc('bool', Sort::BOOL),
                    SortStep::asc('num'),
                ],
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::desc('bool', Sort::BOOL),
                    SortStep::asc('num'),
                ],
                $this->toObjArray([$this->first, $this->second, $this->third])
            ],
            [
                $arrayObj,
                [
                    SortStep::asc('null', Sort::NULL),
                    SortStep::asc('num'),
                ],
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
            [
                $arrayObj,
                [
                    SortStep::desc('null', Sort::NULL),
                    SortStep::asc('num'),
                ],
                $this->toObjArray([$this->second, $this->first, $this->third])
            ],
            [
                $arrayObj,
                [
                    SortStep::asc('date', Sort::DATE),
                ],
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::desc('date', Sort::DATE),
                ],
                $this->toObjArray([$this->first, $this->second, $this->third])
            ],
            [
                [
                    ['id' => 1, 'name' => 'Alex'],
                    ['id' => 2, 'name' => 'Peter'],
                    ['id' => 3, 'name' => 'Alex'],
                ],
                [
                    SortStep::asc('name', Sort::STRING),
                    SortStep::desc('id'),
                ],
                [
                    ['id' => 3, 'name' => 'Alex'],
                    ['id' => 1, 'name' => 'Alex'],
                    ['id' => 2, 'name' => 'Peter'],
                ]
            ],
            [
                [
                    ['v' => false],
                    ['v' => false],
                    ['v' => true],
                    ['v' => true],
                ],
                [
                    SortStep::asc('v', Sort::BOOL),
                ],
                [
                    ['v' => false],
                    ['v' => false],
                    ['v' => true],
                    ['v' => true],
                ],
            ],
        ];
    }

    /**
     * Converts array of arrays to array of SortDummy.
     *
     * @param array $array
     * @return SortDummy[]
     */
    private function toObjArray(array $array) : array
    {
        return array_map(
            function (array $item) {
                return new SortDummy(...array_values($item));
            },
            $array
        );
    }

    /**
     * @dataProvider byProvider
     */
    public function testBy(array $array, string $field, ?string $dir, array $expected) : void
    {
        $actual = Sort::by($array, $field, $dir);

        $this->assertEquals($expected, $actual);
    }

    public function byProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];
        $field = 'num';

        return [
            [$array, $field, null, $array],
            [$array, $field, Sort::ASC, $array],
            [
                $array,
                $field,
                Sort::DESC,
                [$this->third, $this->second, $this->first]
            ],
        ];
    }

    /**
     * @dataProvider ascProvider
     */
    public function testAsc(array $array, string $field, ?string $type, array $expected) : void
    {
        $actual = Sort::asc($array, $field, $type);

        $this->assertEquals($expected, $actual);
    }

    public function ascProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        return [
            [$array, 'num', null, $array],
            [
                $array,
                'str',
                Sort::STRING,
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                'date',
                Sort::DATE,
                [$this->third, $this->second, $this->first]
            ],
            [$arrayObj, 'num', null, $arrayObj],
            [
                $arrayObj,
                'str',
                Sort::STRING,
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
            [
                $arrayObj,
                'date',
                Sort::DATE,
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
        ];
    }

    /**
     * @dataProvider descProvider
     */
    public function testDesc(array $array, string $field, ?string $type, array $expected) : void
    {
        $actual = Sort::desc($array, $field, $type);

        $this->assertEquals($expected, $actual);
    }

    public function descProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        return [
            [
                $array,
                'num',
                null,
                [$this->third, $this->second, $this->first]
            ],
            [
                $array,
                'str',
                Sort::STRING,
                [$this->second, $this->third, $this->first]
            ],
            [
                $array,
                'date',
                Sort::DATE,
                [$this->first, $this->second, $this->third]
            ],
            [
                $arrayObj,
                'num',
                null,
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
            [
                $arrayObj,
                'str',
                Sort::STRING,
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
            [
                $arrayObj,
                'date',
                Sort::DATE,
                $this->toObjArray([$this->first, $this->second, $this->third])
            ],
        ];
    }

    /**
     * @dataProvider byStrProvider
     */
    public function testByStr(array $array, string $field, ?string $dir, array $expected) : void
    {
        $actual = Sort::byStr($array, $field, $dir);

        $this->assertEquals($expected, $actual);
    }

    public function byStrProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];
        $field = 'str';

        return [
            [
                $array,
                $field,
                null,
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                $field,
                Sort::ASC,
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                $field,
                Sort::DESC,
                [$this->second, $this->third, $this->first]
            ],
        ];
    }

    /**
     * @dataProvider ascStrProvider
     */
    public function testAscStr(array $array, string $field, array $expected) : void
    {
        $actual = Sort::ascStr($array, $field);

        $this->assertEquals($expected, $actual);
    }

    public function ascStrProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        $field = 'str';

        return [
            [
                $array,
                $field,
                [$this->first, $this->third, $this->second]
            ],
            [
                $arrayObj,
                $field,
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
        ];
    }

    /**
     * @dataProvider descStrProvider
     */
    public function testDescStr(array $array, string $field, array $expected) : void
    {
        $actual = Sort::descStr($array, $field);

        $this->assertEquals($expected, $actual);
    }

    public function descStrProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        $field = 'str';

        return [
            [
                $array,
                $field,
                [$this->second, $this->third, $this->first]
            ],
            [
                $arrayObj,
                $field,
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
        ];
    }
}
