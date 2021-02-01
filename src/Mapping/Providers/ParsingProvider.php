<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Parsing\Factories\BBContainerParserFactory;
use Plasticode\Parsing\Factories\LineParserFactory;
use Plasticode\Parsing\Factories\ParserFactory;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\LineParser;

class ParsingProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            BBContainerParser::class => BBContainerParserFactory::class,
            LineParser::class => LineParserFactory::class,
            ParserInterface::class => ParserFactory::class,
            ReplacesConfigInterface::class => ReplacesConfig::class,
        ];
    }
}
