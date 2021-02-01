<?php

namespace Plasticode\Tests\Mapping;

use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Mapping\Providers\ParsingProvider;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\Parsers\LineParser;

final class ParsingProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            LinkerInterface::class,
            RendererInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new ParsingProvider();
    }

    public function testWiring(): void
    {
        $this->check(ReplacesConfigInterface::class, ReplacesConfig::class);
        $this->check(BBParserConfig::class);
        $this->check(DoubleBracketsConfig::class);
        $this->check(BBContainerConfig::class);
        $this->check(CleanupParser::class);
        $this->check(BBParser::class);
        $this->check(DoubleBracketsParser::class);
        $this->check(LineParser::class);
        $this->check(CutParser::class);
        $this->check(BBContainerParser::class);
        $this->check(ParserInterface::class, CompositeParser::class);
    }
}
