<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Steps\TitlesStep;

final class TitlesStepTest extends ParsingStepTestCase
{
    /** @var ParsingStepInterface */
    private $lineParser;

    /** @var TitlesStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        // dummy parser for now
        $this->lineParser = new CompositeParser();

        $this->step = new TitlesStep($this->renderer, $this->lineParser);
    }

    protected function tearDown() : void
    {
        unset($this->step);
        unset($this->lineParser);

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
        $lines = [
            '## Title',
            '[b]Hello[/b]',
            '## Some more',
            'Yay text lol'
        ];

        $context = $this->parseLines($lines);
        $resultLines = $context->getLines();

        $this->assertEquals(
            [
                '<h2 id="1">Title</h2>',
                '[b]Hello[/b]',
                '<h2 id="2">Some more</h2>',
                'Yay text lol'
            ],
            $resultLines
        );

        $this->assertCount(2, $context->contents);

        $contents1 = $context->contents[0];
        $contents2 = $context->contents[1];

        $this->assertEquals(
            [1, '1', 'Title'],
            [
                $contents1->level(),
                $contents1->label(),
                $contents1->text()
            ]
        );

        $this->assertEquals(
            [1, '2', 'Some more'],
            [
                $contents2->level(),
                $contents2->label(),
                $contents2->text()
            ]
        );

        $this->assertEmpty($context->largeImages);
        $this->assertEmpty($context->images);
        $this->assertEmpty($context->videos);

        $this->assertNull($context->largeImage());
        $this->assertNull($context->image());
        $this->assertNull($context->video());

        $this->assertNull($context->updatedAt);
    }

    public function testOutOfRangeTitles() : void
    {
        $lines = [
            '# Title',
            '## Title',
            '### Title',
            '#### Title',
            '##### Title',
            '###### Title',
            '####### Title',
            //'## Ima new cool subtitle with [[Tag]]',
        ];

        $context = $this->parseLines($lines);
        $resultLines = $context->getLines();

        $this->assertEquals(
            [
                '# Title',
                '<h2 id="1">Title</h2>',
                '<h3 id="1_1">Title</h3>',
                '<h4 id="1_1_1">Title</h4>',
                '<h5 id="1_1_1_1">Title</h5>',
                '<h6 id="1_1_1_1_1">Title</h6>',
                '####### Title',
            ],
            $resultLines
        );
    }

    public function testScreenedTitle() : void
    {
        $lines = [
            '## Title#',
        ];

        $context = $this->parseLines($lines);
        $resultLines = $context->getLines();

        $this->assertEquals(
            [
                '<h2>Title</h2>',
            ],
            $resultLines
        );

        $this->assertEmpty($context->contents);
    }

    /**
     * Deprecated syntax, only for compatibility.
     */
    public function testStickTitles() : void
    {
        $lines = [
            '|Title|',
            '||Title||',
            '||Title',
            '|||Title|||||||',
            '||||Title',
            '|||||Title||',
            '||||||Title||',
            '|||||||Title||',
            //'||Subtitle ||||| with [[Tag]]||',
            //'|||Sub | title2||',
            //'|||Subtitle2 with [[Tag]]||',
        ];

        $context = $this->parseLines($lines);
        $resultLines = $context->getLines();

        $this->assertEquals(
            [
                '|Title|',
                '<h2 id="1">Title</h2>',
                '<h2 id="2">Title</h2>',
                '<h3 id="2_1">Title</h3>',
                '<h4 id="2_1_1">Title</h4>',
                '<h5 id="2_1_1_1">Title</h5>',
                '<h6 id="2_1_1_1_1">Title</h6>',
                '<h6 id="2_1_1_1_2">Title</h6>',
            ],
            $resultLines
        );
    }
}
