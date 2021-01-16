<?php

namespace Plasticode\Parsing\Factories;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\ParserInterface;
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
use Psr\Container\ContainerInterface;

/**
 * Main parser factory.
 */
class ParserFactory
{
    public function __invoke(ContainerInterface $container): ParserInterface
    {
        $steps = [
            new TitlesStep(
                $container->get(RendererInterface::class),
                $container->get(LineParser::class)
            ),

            new MarkdownParser(
                $container->get(RendererInterface::class)
            ),

            new NewLinesToBrsStep(),

            $container->get(BBContainerParser::class),

            $container->get(BBParser::class),

            new ReplacesStep(
                $container->get(ReplacesConfigInterface::class)
            ),

            $container->get(DoubleBracketsParser::class),

            $container->get(CleanupParser::class)
        ];

        return new CompositeParser(...$steps);
    }
}
