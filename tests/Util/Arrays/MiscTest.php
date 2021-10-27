<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Util\Arrays;

final class MiscTest extends TestCase
{
    const ABC = ['a', 'b', 'c'];

    const AvBvCv = [
        'a' => 'av',
        'b' => 'bv',
        'c' => 'cv'
    ];

    const A_Bc = [
        'a' => ['b' => 'c']
    ];

    /**
     * @dataProvider existsProvider
     * 
     * @param string|integer|null $key
     */
    public function testExists(array $array, $key, bool $result) : void
    {
        $this->assertEquals($result, Arrays::exists($array, $key));
    }

    public function existsProvider() : array
    {
        return [
            [[], null, false],
            [[], 'a', false],
            [self::ABC, null, false],
            [self::ABC, 'a', false],
            [self::ABC, 1, true],
            [self::ABC, 5, false],
            [self::AvBvCv, null, false],
            [self::AvBvCv, 'b', true],
            [self::AvBvCv, 'd', false],
            [self::AvBvCv, 1, false],
        ];
    }

    /**
     * @dataProvider getProvider
     *
     * @param string|integer|null $key
     * @param mixed $result
     */
    public function testGet(array $array, $key, $result) : void
    {
        $this->assertEquals($result, Arrays::get($array, $key));
    }

    public function getProvider() : array
    {
        return [
            [[], null, null],
            [[], 'a', null],
            [self::ABC, null, null],
            [self::ABC, 'a', null],
            [self::ABC, 1, 'b'],
            [self::ABC, 5, null],
            [self::AvBvCv, null, null],
            [self::AvBvCv, 'b', 'bv'],
            [self::AvBvCv, 'd', null],
            [self::A_Bc, 'a.b', 'c'],
        ];
    }

    /**
     * @dataProvider setProvider
     *
     * @param string|integer $key
     * @param mixed $value
     */
    public function testSet(array $array, $key, $value) : void
    {
        Arrays::set($array, $key, $value);

        $this->assertEquals($value, Arrays::get($array, $key));
    }

    public function setProvider() : array
    {
        $v = 'v';

        return [
            [[], 'a', $v],
            [[], 'a.b', $v],
            [self::ABC, 'a', $v],
            [self::ABC, 1, $v],
            [self::AvBvCv, 'b', $v],
            [self::AvBvCv, 'd', $v],
            [self::A_Bc, 'a.b', $v],
        ];
    }

    /**
     * @dataProvider setNullKeyProvider
     *
     * @param mixed $value
     */
    public function testSetNullKey(array $array, $value) : void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arrays::set($array, null, $value);
    }

    public function setNullKeyProvider() : array
    {
        $v = 'v';

        return [
            [[], $v],
            [self::ABC, $v],
            [self::AvBvCv, $v],
            [self::A_Bc, $v],
        ];
    }

    /**
     * @dataProvider cleanProvider
     */
    public function testClean(array $array, array $result) : void
    {
        $this->assertEquals($result, Arrays::clean($array));
    }

    public function cleanProvider() : array
    {
        return [
            [[], []],
            [
                [
                    'some',
                    '',
                    'string',
                    null,
                    [1, 2, 3],
                    0
                ],
                ['some', 'string', [1, 2, 3]]
            ],
            [
                ['already', 'clean'],
                ['already', 'clean']
            ],
        ];
    }

    /**
     * @dataProvider trimProvider
     */
    public function testTrim(array $array, array $expected) : void
    {
        $this->assertEquals($expected, Arrays::trim($array));
    }

    public function trimProvider() : array
    {
        return [
            [[], []],
            [
                [
                    'some  ',
                    '   ',
                    '  string',
                    null
                ],
                ['some', 'string']
            ],
            [
                ['already', 'clean'],
                ['already', 'clean']
            ],
        ];
    }

    public function testShuffle() : void
    {
        $original = self::ABC;
        $shuffled = Arrays::shuffle($original);

        $this->assertSameSize($shuffled, $original);
        $this->assertEqualsCanonicalizing($shuffled, $original);
    }

    /**
     * @dataProvider removeFirstByProvider
     */
    public function testRemoveFirstBy(array $original, array $expected, callable $by) : void
    {
        $this->assertEquals(
            $expected,
            Arrays::removeFirstBy($original, $by)
        );
    }

    public function removeFirstByProvider() : array
    {
        $dummy1 = new ModelDummy(1, 'one');
        $dummy2 = new ModelDummy(2, 'two');
        $dummy3 = new ModelDummy(3, 'three');

        $array = [$dummy1, $dummy2, $dummy3, $dummy2, $dummy3];

        return [
            [
                $array,
                [$dummy2, $dummy3, $dummy2, $dummy3],
                fn (ModelDummy $d) => $d->id == 1
            ],
            [
                $array,
                [$dummy1, $dummy3, $dummy2, $dummy3],
                fn (ModelDummy $d) => $d->id == 2
            ],
            [
                $array,
                [$dummy1, $dummy2, $dummy2, $dummy3],
                fn (ModelDummy $d) => $d->id == 3
            ],
            [
                $array,
                [$dummy1, $dummy2, $dummy3, $dummy2, $dummy3],
                fn (ModelDummy $d) => $d->id == 4
            ]
        ];
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains(array $first, array $second, bool $expected): void
    {
        $this->assertEquals(
            $expected,
            Arrays::contains($first, $second)
        );
    }

    public function containsProvider(): array
    {
        return [
            [[], [], true],
            [
                [1, 2, 3],
                [],
                true
            ],
            [
                [1, 2, 3],
                [1],
                true
            ],
            [
                [1, 2, 3],
                [1, 2],
                true
            ],
            [
                [1, 2, 3],
                [3, 2, 1],
                true
            ],
            [
                [1, 2, 3],
                [4],
                false
            ],
        ];
    }
}
