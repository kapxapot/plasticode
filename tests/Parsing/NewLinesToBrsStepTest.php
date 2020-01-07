<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\NewLinesToBrsStep;

final class NewLinesToBrsStepTest extends ParsingTestCase
{
    /** @var NewLinesToBrsStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        $this->step = new NewLinesToBrsStep();
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
     * @covers NewLinesToBrsStep
     */
    public function testContextIsImmutable() : void
    {
        $this->assertContextIsImmutable();
    }

    /**
     * @covers NewLinesToBrsStep
     */
    public function testParse() : void
    {
        $context = $this->parseLines(
            [
                'Some text',
                'with',
                '',
                'line breaks'
            ]
        );

        $this->assertEquals(
            'Some text<br/>with<br/><br/>line breaks',
            $context->text
        );
    }
}
