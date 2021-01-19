<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Parsing\Factories\BBContainerParserFactory;
use Plasticode\Parsing\Factories\ParserFactory;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\Parsers\LineParser;
use Psr\Container\ContainerInterface;

class ParsingProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            ReplacesConfigInterface::class =>
                fn (ContainerInterface $c) => new ReplacesConfig(),

            BBParserConfig::class =>
                fn (ContainerInterface $c) => new BBParserConfig(
                    $c->get(LinkerInterface::class)
                ),

            // no double brackets link mappers by default
            // add them!
            DoubleBracketsConfig::class =>
                fn (ContainerInterface $c) => new DoubleBracketsConfig(),

            BBContainerConfig::class =>
                fn (ContainerInterface $c) => new BBContainerConfig(),

            CleanupParser::class =>
                fn (ContainerInterface $c) => new CleanupParser(
                    $c->get(ReplacesConfigInterface::class)
                ),

            BBParser::class =>
                fn (ContainerInterface $c) => new BBParser(
                    $c->get(BBParserConfig::class),
                    $c->get(RendererInterface::class)
                ),

            DoubleBracketsParser::class =>
                fn (ContainerInterface $c) => new DoubleBracketsParser(
                    $c->get(DoubleBracketsConfig::class)
                ),

            LineParser::class =>
                fn (ContainerInterface $c) => new LineParser(
                    $c->get(BBParser::class),
                    $c->get(DoubleBracketsParser::class)
                ),

            CutParser::class =>
                fn (ContainerInterface $c) => new CutParser(
                    $c->get(CleanupParser::class)
                ),

            BBContainerParser::class => BBContainerParserFactory::class,

            // main parser
            ParserInterface::class => ParserFactory::class,
        ];
    }
}
