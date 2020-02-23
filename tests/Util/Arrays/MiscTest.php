<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;

final class MiscTest extends TestCase
{
    /** @var array */
    const ABC = ['a', 'b', 'c'];

    /** @var array */
    const AvBvCv = [
        'a' => 'av',
        'b' => 'bv',
        'c' => 'cv'
    ];

    /** @var array */
    const A_Bc = [
        'a' => ['b' => 'c']
    ];

    /**
     * @dataProvider existsProvider
     * 
     * @param array $array
     * @param string|integer|null $key
     * @param boolean $result
     * @return void
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
     * @param array $array
     * @param string|integer|null $key
     * @param mixed $result
     * @return void
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
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return void
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
     * @param array $array
     * @param mixed $value
     * @return void
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
     *
     * @param array $array
     * @param array $result
     * @return void
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
}
