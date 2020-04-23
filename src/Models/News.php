<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

class News extends DbModel implements TaggedInterface
{
    use FullPublished;
    use Tagged;

    protected function requiredWiths(): array
    {
        return ['tagLinks'];
    }
}
