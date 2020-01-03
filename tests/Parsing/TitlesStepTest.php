<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\Parsing as ParsingConfig;
use Plasticode\Core\Renderer;
use Plasticode\Parsing\Parser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\TitlesStep;
use Slim\Views\Twig;

final class TitlesStepTest extends TestCase
{
    public function testParse() : void
    {
        $view = new Twig('views/bootstrap3/', ['debug' => true]);

        $renderer = new Renderer($view);

        $config = new ParsingConfig();
        $lineParser = new Parser($config, $renderer); // dummy parser for now

        $step = new TitlesStep($renderer, $lineParser);

        $lines = [
            '## Title',
            '[b]Hello[/b]',
            '## Some more',
            'Yay text lol'
        ];
        
        $context = ParsingContext::fromLines($lines);
        $context = $step->parse($context);

        $resultLines = $context->getLines();

        $this->assertEquals(
            [
                count($lines),
                '<p class="subtitle subtitle1" id="1">Title</p>',
                '[b]Hello[/b]',
                '<p class="subtitle subtitle1" id="2">Some more</p>',
                'Yay text lol',
                1,
                '1',
                'Title',
                1,
                '2',
                'Some more',
                0,
                0,
                0,
                null
            ],
            [
                count($resultLines),
                $resultLines[0],
                $resultLines[1],
                $resultLines[2],
                $resultLines[3],
                $context->contents->first()->level,
                $context->contents->first()->label,
                $context->contents->first()->text,
                $context->contents->skip(1)->first()->level,
                $context->contents->skip(1)->first()->label,
                $context->contents->skip(1)->first()->text,
                count($context->largeImages),
                count($context->images),
                count($context->videos),
                $context->updatedAt
            ]
        );
    }
}
