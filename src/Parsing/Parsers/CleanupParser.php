<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Parsing\Steps\BrsToPsStep;
use Plasticode\Parsing\Steps\CleanupStep;

class CleanupParser extends CompositeParser
{
    public function __construct(ParsingConfigInterface $config)
    {
        parent::__construct(
            [
                new BrsToPsStep(),
                new CleanupStep($config)
            ]
        );
    }
}
