<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\TitlesContext;

final class TitlesContextTest extends TestCase
{
    /**
     * @covers TitlesContext
     */
    public function testNonPositiveLimits() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TitlesContext(0, 10);
    }

    /**
     * @covers TitlesContext
     */
    public function testInvalidLimits() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TitlesContext(2, 1);
    }

    /**
     * @covers TitlesContext
     */
    public function testCorrectLimitsAndSlices() : void
    {
        $context = new TitlesContext(1, 4);

        $this->assertCount(1, $context->getCountSlice(1));
        $this->assertCount(2, $context->getCountSlice(2));
        $this->assertCount(3, $context->getCountSlice(3));
        $this->assertCount(4, $context->getCountSlice(4));
    }

    /**
     * @covers TitlesContext
     */
    public function testIncorrectSliceTooLow() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $context = new TitlesContext(1, 4);
        $context->getCountSlice(0);
    }

    /**
     * @covers TitlesContext
     */
    public function testIncorrectSliceTooHigh() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $context = new TitlesContext(1, 4);
        $context->getCountSlice(5);
    }
}
