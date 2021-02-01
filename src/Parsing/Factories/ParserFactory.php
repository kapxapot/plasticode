<?php

namespace Plasticode\Parsing\Factories;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\Parsers\LineParser;
use Plasticode\Parsing\Parsers\MarkdownParser;
use Plasticode\Parsing\Steps\NewLinesToBrsStep;
use Plasticode\Parsing\Steps\ReplacesStep;
use Plasticode\Parsing\Steps\TitlesStep;

/**
 * Main parser factory.
 */
class ParserFactory
{
    public function __invoke(
        BBContainerParser $bbContainerParser,
        BBParser $bbParser,
        CleanupParser $cleanupParser,
        DoubleBracketsParser $doubleBracketsParser,
        LineParser $lineParser,
        RendererInterface $renderer,
        ReplacesConfigInterface $replacesConfig
    ): CompositeParser
    {
        return new CompositeParser(
            new TitlesStep($renderer, $lineParser),
            new MarkdownParser($renderer),
            new NewLinesToBrsStep(),
            $bbContainerParser,
            $bbParser,
            new ReplacesStep($replacesConfig),
            $doubleBracketsParser,
            $cleanupParser
        );
    }
}
