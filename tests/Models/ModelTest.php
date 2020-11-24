<?php

namespace Plasticode\Tests\Models;

use PHPUnit\Framework\TestCase;
use Plasticode\Models\Basic\Model;

final class ModelTest extends TestCase
{
    /**
     * @dataProvider jsonEncodeProvider
     */
    public function testJsonEncode(Model $original, array $expected) : void
    {
        $actual = json_encode($original);

        $this->assertEquals(json_encode($expected), $actual);
    }

    public function jsonEncodeProvider()
    {
        return [
            [
                new Model(),
                []
            ],
            [
                new Model(
                    [
                        'a' => 1,
                        'b' => 2,
                        'c' => '3'
                    ]
                ),
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => '3'
                ]
            ],
        ];
    }
}
