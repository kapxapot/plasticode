<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Steps\BrsToPsStep;
use Plasticode\Parsing\Steps\CleanupStep;

class CleanupParser extends CompositeParser
{
    public function __construct(ParsingConfigInterface $config, RendererInterface $renderer)
    {
        parent::__construct(
            $config,
            $renderer,
            [
                new BrsToPsStep(),
                new CleanupStep($config)
            ]
        );
    }
}
