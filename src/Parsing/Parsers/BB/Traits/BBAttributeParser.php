<?php

namespace Plasticode\Parsing\Parsers\BB\Traits;

trait BBAttributeParser
{
    public function parseAttributes(string $str) : array
    {
        $attrsStr = trim($str, ' |=');
        $attrs = preg_split('/\|/', $attrsStr, -1, PREG_SPLIT_NO_EMPTY);
        
        return $attrs;
    }
}
