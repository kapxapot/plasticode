<?php

namespace Plasticode\Models\Traits;

trait Description
{
    public function parsedDescription() : ?string
    {
        $context = self::$parser->parse($this->description);
        $context = self::$parser->renderLinks($context);

        return $context->text;
    }
}
