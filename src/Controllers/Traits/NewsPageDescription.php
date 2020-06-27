<?php

namespace Plasticode\Controllers\Traits;

use Plasticode\Models\Interfaces\NewsSourceInterface;

trait NewsPageDescription
{
    use PageDescription;

    protected function makeNewsPageDescription(
        NewsSourceInterface $news,
        string $limitVar = null
    ) : string
    {
        return $this->makePageDescription($news->fullText(), $limitVar);
    }
}
