<?php

namespace Plasticode\Tests\Util\Arrays;

use PHPUnit\Framework\TestCase;
use Plasticode\Models\Basic\Model;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Util\Arrays;

final class ExtractTest extends TestCase
{
    /**
     * @dataProvider extractProvider
     */
    public function testExtract(array $array, string $column, array $result) : void
    {
        $this->assertEquals($result, Arrays::extract($array, $column));
    }

    public function extractProvider() : array
    {
        $item1 = ['id' => 1, 'name' => 'one'];
        $item11 = ['id' => 1, 'name' => 'one one'];
        $item2 = ['id' => 2, 'name' => 'one'];

        $dummy1 = new ModelDummy(1, 'one');
        $dummy11 = new ModelDummy(1, 'one one');
        $dummy2 = new ModelDummy(2, 'one');

        $model1 = new Model($item1);
        $model11 = new Model($item11);
        $model2 = new Model($item2);

        return [
            [[], 'a', []],
            [
                [$item1, $item11, $item2],
                'name',
                ['one', 'one one']
            ],
            [
                [$dummy1, $dummy11, $dummy2],
                'name',
                ['one', 'one one']
            ],
            [
                [$model1, $model11, $model2],
                'name',
                ['one', 'one one']
            ]
        ];
    }
}
