<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Parsing\Steps\BrsToPsStep;
use Plasticode\Parsing\Steps\CleanupStep;

class CleanupParser extends CompositeParser
{
    public function __construct(ReplacesConfigInterface $config)
    {
        parent::__construct(
            [
                new BrsToPsStep(),
                new CleanupStep($config)
            ]
        );
    }
}
