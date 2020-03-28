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
     * @dataProvider multiProvider
     * 
     * @param SortStep[] $steps
     */
    public function testMulti(array $array, array $steps, array $expected) : void
    {
        $actual = Sort::multi($array, $steps);

        $this->assertEquals($expected, $actual);
    }

    public function multiProvider() : array
    {
        $array = [$this->first, $this->second, $this->third];

        $arrayObj = $this->toObjArray($array);

        return [
            [[], [], []],
            [$array, [], $array],
            [
                $array,
                [
                    SortStep::create('num'),
                ],
                $array
            ],
            [
                $array,
                [
                    SortStep::createDesc('num'),
                ],
                [$this->third, $this->second, $this->first]
            ],
            [
                $array,
                [
                    SortStep::create('str')->withType(Sort::STRING),
                ],
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                [
                    SortStep::createDesc('str')->withType(Sort::STRING),
                ],
                [$this->second, $this->third, $this->first]
            ],
            [
                $array,
                [
                    SortStep::create('bool')->withType(Sort::BOOL),
                    SortStep::create('num'),
                ],
                [$this->second, $this->third, $this->first]
            ],
            [
                $array,
                [
                    SortStep::createDesc('bool')->withType(Sort::BOOL),
                    SortStep::create('num'),
                ],
                [$this->first, $this->second, $this->third]
            ],
            [
                $array,
                [
                    SortStep::create('null')->withType(Sort::NULL),
                    SortStep::create('num'),
                ],
                [$this->first, $this->third, $this->second]
            ],
            [
                $array,
                [
                    SortStep::createDesc('null')->withType(Sort::NULL),
                    SortStep::create('num'),
                ],
                [$this->second, $this->first, $this->third]
            ],
            [
                $array,
                [
                    SortStep::create('date')->withType(Sort::DATE),
                ],
                [$this->third, $this->second, $this->first]
            ],
            [
                $array,
                [
                    SortStep::createDesc('date')->withType(Sort::DATE),
                ],
                [$this->first, $this->second, $this->third]
            ],
            [$arrayObj, [], $arrayObj],
            [
                $arrayObj,
                [
                    SortStep::create('num'),
                ],
                $arrayObj
            ],
            [
                $arrayObj,
                [
                    SortStep::createDesc('num'),
                ],
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::create('str')->withType(Sort::STRING),
                ],
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
            [
                $arrayObj,
                [
                    SortStep::createDesc('str')->withType(Sort::STRING),
                ],
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::create('bool')->withType(Sort::BOOL),
                    SortStep::create('num'),
                ],
                $this->toObjArray([$this->second, $this->third, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::createDesc('bool')->withType(Sort::BOOL),
                    SortStep::create('num'),
                ],
                $this->toObjArray([$this->first, $this->second, $this->third])
            ],
            [
                $arrayObj,
                [
                    SortStep::create('null')->withType(Sort::NULL),
                    SortStep::create('num'),
                ],
                $this->toObjArray([$this->first, $this->third, $this->second])
            ],
            [
                $arrayObj,
                [
                    SortStep::createDesc('null')->withType(Sort::NULL),
                    SortStep::create('num'),
                ],
                $this->toObjArray([$this->second, $this->first, $this->third])
            ],
            [
                $arrayObj,
                [
                    SortStep::create('date')->withType(Sort::DATE),
                ],
                $this->toObjArray([$this->third, $this->second, $this->first])
            ],
            [
                $arrayObj,
                [
                    SortStep::createDesc('date')->withType(Sort::DATE),
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
                    SortStep::create('name')->withType(Sort::STRING),
                    SortStep::createDesc('id'),
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
                    SortStep::create('v')->withType(Sort::BOOL),
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
