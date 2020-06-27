<?php

namespace Plasticode\Controllers\Traits;

use Plasticode\Util\Strings;

trait PageDescription
{
    protected int $pageDescriptionLimit = 1000;

    protected function makePageDescription(string $text, ?string $limitVar = null) : string
    {
        $limit = $limitVar
            ? $this->getSettings(
                $limitVar,
                $this->pageDescriptionLimit
            )
            : $this->pageDescriptionLimit;

        return Strings::stripTrunc($text, $limit);
    }

    abstract protected function getSettings(string $path, $default = null);
}
