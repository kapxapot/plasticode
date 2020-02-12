<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\ReplacesStep;

/**
 * @covers \Plasticode\Parsing\Steps\ReplacesStep
 */
final class ReplacesStepTest extends ParsingStepTestCase
{
    /** @var ReplacesStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        $this->step = new ReplacesStep($this->config);
    }

    protected function tearDown() : void
    {
        unset($this->step);

        parent::tearDown();
    }

    protected function step() : ParsingStepInterface
    {
        return $this->step;
    }

    public function testContextIsImmutable() : void
    {
        $this->assertContextIsImmutable();
    }

    public function testParse() : void
    {
        $context = $this->parseLines(
            [
                '[b]Some[/b] text',
                '[i]with[/i]',
                'a -- b',
                'line [s]breaks[/s]'
            ]
        );

        $this->assertEquals(
            [
                '<b>Some</b> text',
                '<i>with</i>',
                'a — b',
                'line <strike>breaks</strike>'
            ],
            $context->getLines()
        );
    }
}
