<?php

namespace Plasticode\Models\Traits;

use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\ParsingContext;

trait Description
{
    public function parsedDescription(ParserInterface $parser) : ?ParsingContext
    {
        if (strlen($this->description) == 0) {
            return null;
        }

        $context = $parser->parse($this->description);
        $context = $parser->renderLinks($context);

        return $context;
    }
}
