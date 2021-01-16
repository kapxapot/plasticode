<?php

namespace Plasticode\Parsing\Factories;

use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;
use Psr\Container\ContainerInterface;

class BBContainerParserFactory
{
    public function __invoke(ContainerInterface $container): BBContainerParser
    {
        return new BBContainerParser(
            $container->get(BBContainerConfig::class),
            new BBSequencer(),
            new BBTreeBuilder(),
            new BBTreeRenderer(
                $container->get(RendererInterface::class)
            )
        );
    }
}
