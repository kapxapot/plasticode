<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Util\Arrays;

final class SliceTest extends TestCase
{
    /**
     * @dataProvider skipProvider
     */
    public function testSkip(array $array, int $offset, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::skip($array, $offset)
        );
    }

    public function skipProvider() : array
    {
        $testArray = ['one', 'two', 'three', 'four', 'five'];

        return [
            [[], 0, []],
            [[], 1, []],
            [[], -1, []],
            [$testArray, 0, $testArray],
            [$testArray, 1, ['two', 'three', 'four', 'five']],
            [$testArray, -1, ['five']],
            [$testArray, 10, []],
            [$testArray, -10, $testArray]
        ];
    }

    /**
     * @dataProvider takeProvider
     */
    public function testTake(array $array, int $limit, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::take($array, $limit)
        );
    }

    public function takeProvider() : array
    {
        $testArray = ['one', 'two', 'three', 'four', 'five'];

        return [
            [[], 0, []],
            [[], 1, []],
            [[], -1, []],
            [$testArray, 0, []],
            [$testArray, 1, ['one']],
            [$testArray, -1, ['one', 'two', 'three', 'four']],
            [$testArray, 10, $testArray],
            [$testArray, -10, []]
        ];
    }

    /**
     * @dataProvider sliceProvider
     */
    public function testSlice(array $array, int $offset, int $limit, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::slice($array, $offset, $limit)
        );
    }

    public function sliceProvider() : array
    {
        $testArray = ['one', 'two', 'three', 'four', 'five'];

        return [
            [[], 0, 0, []], // 0

            [[], 0, 1, []], // 1
            [[], 1, 0, []], // 2
            [[], 0, -1, []], // 3
            [[], -1, 0, []], // 4
            [[], 1, 1, []], // 5
            [[], -1, -1, []], // 6

            [$testArray, 0, 0, []], // 7
            [$testArray, 1, 0, []], // 8
            [$testArray, -1, 0, []], // 9
            [$testArray, -1, -1, []], // 10
            [$testArray, 10, 0, []], // 11
            [$testArray, 10, 1, []], // 12
            [$testArray, 10, -1, []], // 13
            [$testArray, 0, -10, []], // 14
            [$testArray, -10, 0, []], // 15
            [$testArray, 1, -10, []], // 16
            [$testArray, -1, -10, []], // 17
            [$testArray, 10, 10, []], // 18
            [$testArray, 10, -10, []], // 19
            [$testArray, -10, -10, []], // 20

            [$testArray, 0, 1, ['one']], // 21
            [$testArray, -10, 1, ['one']], // 22

            [$testArray, 1, 1, ['two']], // 23

            [$testArray, -1, 1, ['five']], // 24
            [$testArray, -1, 10, ['five']], // 25

            [$testArray, 0, -1, ['one', 'two', 'three', 'four']], // 26
            [$testArray, -10, -1, ['one', 'two', 'three', 'four']], // 27

            [$testArray, 1, 10, ['two', 'three', 'four', 'five']], // 28

            [$testArray, 1, -1, ['two', 'three', 'four']], // 29

            [$testArray, 0, 10, $testArray], // 30
            [$testArray, -10, 10, $testArray], // 31
        ];
    }

    /**
     * @dataProvider trimTailProvider
     */
    public function testTrimTail(array $array, int $limit = null, array $result) : void
    {
        $this->assertEquals(
            $result,
            Arrays::trimTail($array, $limit)
        );
    }

    public function trimTailProvider() : array
    {
        $array = ['one', 'two', 'three'];

        return [
            [[], null, []],
            [[], 1, []],
            [[], 3, []],
            [[], 5, []],
            [$array, null, ['one', 'two']],
            [$array, 1, ['one', 'two']],
            [$array, 2, ['one']],
            [$array, 3, []],
            [$array, 5, []],
        ];
    }
}
