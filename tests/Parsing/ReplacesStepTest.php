<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\ReplacesStep;

final class ReplacesStepTest extends ParsingTestCase
{
    /** @var \Plasticode\Parsing\Steps\ReplacesStep */
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

    /**
     * @covers ReplacesStep
     */
    public function testContextIsImmutable() : void
    {
        $this->assertContextIsImmutable();
    }

    /**
     * @covers ReplacesStep
     */
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
