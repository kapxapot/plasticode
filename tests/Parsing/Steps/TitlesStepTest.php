<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Steps\TitlesStep;

/**
 * @covers \Plasticode\Parsing\Steps\TitlesStep
 */
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
                '<p class="subtitle subtitle1" id="1">Title</p>',
                '[b]Hello[/b]',
                '<p class="subtitle subtitle1" id="2">Some more</p>',
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
                $contents1->level,
                $contents1->label,
                $contents1->text
            ]
        );

        $this->assertEquals(
            [1, '2', 'Some more'],
            [
                $contents2->level,
                $contents2->label,
                $contents2->text
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
                '<p class="subtitle subtitle1" id="1">Title</p>',
                '<p class="subtitle subtitle2" id="1_1">Title</p>',
                '<p class="subtitle subtitle3" id="1_1_1">Title</p>',
                '<p class="subtitle subtitle4" id="1_1_1_1">Title</p>',
                '<p class="subtitle subtitle5" id="1_1_1_1_1">Title</p>',
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
                '<p class="subtitle subtitle1">Title</p>',
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
                '<p class="subtitle subtitle1" id="1">Title</p>',
                '<p class="subtitle subtitle1" id="2">Title</p>',
                '<p class="subtitle subtitle2" id="2_1">Title</p>',
                '<p class="subtitle subtitle3" id="2_1_1">Title</p>',
                '<p class="subtitle subtitle4" id="2_1_1_1">Title</p>',
                '<p class="subtitle subtitle5" id="2_1_1_1_1">Title</p>',
                '<p class="subtitle subtitle5" id="2_1_1_1_2">Title</p>',
            ],
            $resultLines
        );
    }
}
