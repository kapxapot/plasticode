<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\CleanupStep;

final class CleanupStepTest extends ParsingStepTestCase
{
    /** @var CleanupStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        $this->step = new CleanupStep($this->config);
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
                '<p><p><p>',
                '</p></p></p>',
                '<p><ul></ul></p>',
                '<p><ol></ol></p>',
                '<p><div></div></p>',
                '<p><figure></figure></p>',
                '<br/><div></div><br/>'
            ]
        );

        $this->assertEquals(
            [
                '<p>',
                '</p>',
                '<ul></ul>',
                '<ol></ol>',
                '<div></div>',
                '<figure></figure>',
                '<div></div>'
            ],
            $context->getLines()
        );
    }
}
