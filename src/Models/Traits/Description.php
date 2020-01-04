<?php

namespace Plasticode\Models\Traits;

use Plasticode\Parsing\ParsingContext;

trait Description
{
    public function parsedDescription() : ?ParsingContext
    {
        $context = self::$parser->parse($this->description);

        if (is_null($context)) {
            return null;
        }
    
        $context = self::$parser->renderLinks($context);

        return $context;
    }
}
