<?php

namespace Plasticode\Parsing\Factories;

use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\Parsers\LineParser;

class LineParserFactory
{
    public function __invoke(
        BBParser $bbParser,
        DoubleBracketsParser $doubleBracketsParser
    ): LineParser
    {
        return new LineParser(
            $bbParser,
            $doubleBracketsParser
        );
    }
}
