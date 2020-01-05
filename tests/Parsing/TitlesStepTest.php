<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\TitlesStep;

final class TitlesStepTest extends ParsingTestCase
{
    /** @var \Plasticode\Parsing\Parser */
    private $lineParser;

    /** @var \Plasticode\Parsing\Steps\TitlesStep */
    private $step;

    protected function setUp() : void
    {
        parent::setUp();

        $this->lineParser = $this->createParser(); // dummy parser for now

        $this->step = new TitlesStep($this->renderer, $this->lineParser);
    }

    protected function tearDown() : void
    {
        unset($this->lineParser);

        parent::tearDown();
    }

    private function parseLines(array $lines) : ParsingContext
    {
        $context = ParsingContext::fromLines($lines);
        $context = $this->step->parse($context);

        return $context;
    }

    /**
     * @covers TitlesStep
     */
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

    /**
     * @covers TitlesStep
     */
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

    /**
     * @covers TitlesStep
     */
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

        $this->assertCount(0, $context->contents);
    }

    /**
     * Deprecated syntax, only for compatibility.
     * 
     * @covers TitlesStep
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
