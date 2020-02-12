<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\TitlesContext;

/**
 * @covers \Plasticode\Parsing\TitlesContext
 */
final class TitlesContextTest extends TestCase
{
    public function testNonPositiveLimits() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TitlesContext(0, 10);
    }

    public function testInvalidLimits() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TitlesContext(2, 1);
    }

    public function testCorrectLimitsAndSlices() : void
    {
        $context = new TitlesContext(1, 4);

        $this->assertCount(1, $context->getCountSlice(1));
        $this->assertCount(2, $context->getCountSlice(2));
        $this->assertCount(3, $context->getCountSlice(3));
        $this->assertCount(4, $context->getCountSlice(4));
    }

    public function testIncorrectSliceTooLow() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $context = new TitlesContext(1, 4);
        $context->getCountSlice(0);
    }

    public function testIncorrectSliceTooHigh() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $context = new TitlesContext(1, 4);
        $context->getCountSlice(5);
    }
}
