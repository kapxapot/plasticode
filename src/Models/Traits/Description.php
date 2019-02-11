<?php

namespace Plasticode\Models\Traits;

trait Description
{
    public function parsedDescription()
    {
        $text = self::$parser->justText($this->description);
        $text = self::$parser->renderLinks($text);
        
        return $text;
    }
}
