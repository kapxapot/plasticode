<?php

namespace Plasticode\Controllers\Factories;

use Plasticode\Controllers\ParserController;
use Plasticode\Core\Env;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ParserControllerFactory
{
    public function __invoke(ContainerInterface $container): ParserController
    {
        return new ParserController(
            $container->get(Env::class),
            $container->get(LoggerInterface::class),
            $container->get(ParserInterface::class),
            $container->get(CutParser::class)
        );
    }
}
