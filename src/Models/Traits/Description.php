<?php

namespace Plasticode\Models\Traits;

trait Description
{
    public function parsedDescription()
    {
        return self::$parser->justText($this->description);
    }
}
