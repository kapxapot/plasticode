<?php

namespace Plasticode\Parsing\Factories;

use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;

class BBContainerParserFactory
{
    public function __invoke(
        BBContainerConfig $containerConfig,
        BBSequencer $sequencer,
        BBTreeBuilder $treeBuilder,
        BBTreeRenderer $treeRenderer
    ): BBContainerParser
    {
        return new BBContainerParser(
            $containerConfig,
            $sequencer,
            $treeBuilder,
            $treeRenderer
        );
    }
}
