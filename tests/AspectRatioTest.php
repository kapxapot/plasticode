<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Models\AspectRatio;

final class AspectRatioTest extends TestCase
{
    public function testInvalidDimensions() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AspectRatio(0, 10);
    }

    public function testIsHorizontal() : void
    {
        $ratio = new AspectRatio(200, 100);

        $this->assertTrue($ratio->isHorizontal());
    }

    public function testIsVertical() : void
    {
        $ratio = new AspectRatio(100, 200);

        $this->assertTrue($ratio->isVertical());
    }

    /**
     * @dataProvider exactProvider
     */
    public function testExact(int $width, int $height, float $expected) : void
    {
        $ratio = new AspectRatio($width, $height);

        $this->assertEquals($expected, $ratio->exact());
    }

    public function exactProvider()
    {
        return [
            [100, 200, 2],
            [100, 110, 1.1],
        ];
    }

    /**
     * @dataProvider closestProvider
     * 
     * @param integer $width
     * @param integer $height
     * @param integer[] $expected
     * @return void
     */
    public function testClosest(int $width, int $height, array $expected) : void
    {
        $ratio = new AspectRatio($width, $height);

        $this->assertEquals($expected, $ratio->closest());
    }

    public function closestProvider()
    {
        return [
            [100, 110, [1, 1]],
            [1000, 100, [3, 1]],
        ];
    }

    /**
     * @dataProvider cssProvider
     */
    public function testCss(int $width, int $height, string $expected) : void
    {
        $ratio = new AspectRatio($width, $height);

        $this->assertEquals($expected, $ratio->cssClasses());
    }

    public function cssProvider()
    {
        return [
            [100, 110, 'ratio-w1 ratio-h1'],
            [1000, 100, 'ratio-w3 ratio-h1'],
            [100, 90, 'ratio-w1 ratio-h1'],
            [100, 1000, 'ratio-w1 ratio-h3'],
            [20, 30, 'ratio-w2 ratio-h3'],
            [31, 19, 'ratio-w3 ratio-h2'],
        ];
    }

    /**
     * @dataProvider supportedRatiosProvider
     *
     * @param integer $width
     * @param integer $height
     * @param integer[][] $supportedRatios
     * @param integer[] $expected
     * @return void
     */
    public function testSupportedRatios(int $width, int $height, array $supportedRatios, array $expected) : void
    {
        $ratio = new AspectRatio($width, $height, $supportedRatios);

        $this->assertEquals(
            $expected,
            $ratio->closest()
        );
    }

    public function supportedRatiosProvider() : array
    {
        $supportedRatios = [
            [1, 1],
            [1, 10]
        ];

        return [
            [100, 400, $supportedRatios, [1, 1]],
            [600, 100, $supportedRatios, [10, 1]],
        ];
    }
}
