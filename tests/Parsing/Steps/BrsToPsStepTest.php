<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\BrsToPsStep;

/**
 * @covers \Plasticode\Parsing\Steps\BrsToPsStep
 */
final class BrsToPsStepTest extends ParsingStepTestCase
{
    /** @var BrsToPsStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        $this->step = new BrsToPsStep();
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

    public function testParseNotWrapped() : void
    {
        $context = $this->parse(
            'Some text<br/><br/>with<br/>brs.'
        );

        $this->assertEquals(
            '<p>Some text</p><p>with<br/>brs.</p>',
            $context->text
        );
    }

    public function testParseWrapped() : void
    {
        $context = $this->parse(
            '<p>Some text<br/><br/>with<br/>brs.</p>'
        );

        $this->assertEquals(
            '<p>Some text</p><p>with<br/>brs.</p>',
            $context->text
        );
    }

    public function testParseWithStart() : void
    {
        $context = $this->parse(
            '<p>Some text<br/><br/>with<br/>brs.'
        );

        $this->assertEquals(
            '<p>Some text</p><p>with<br/>brs.</p>',
            $context->text
        );
    }

    public function testParseWithEnd() : void
    {
        $context = $this->parse(
            'Some text<br/><br/>with<br/>brs.</p>'
        );

        $this->assertEquals(
            '<p>Some text</p><p>with<br/>brs.</p>',
            $context->text
        );
    }
}
