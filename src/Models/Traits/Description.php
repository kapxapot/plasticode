<?php

namespace Plasticode\Models\Traits;

use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\ParsingContext;

/**
 * @property CompositeParser $parser
 */
trait Description
{
    public function parsedDescription() : ?ParsingContext
    {
        if (strlen($this->description) == 0) {
            return null;
        }

        $context = self::$parser->parse($this->description);
        $context = self::$parser->renderLinks($context);

        return $context;
    }
}
