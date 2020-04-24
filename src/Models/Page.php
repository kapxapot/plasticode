<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class Page extends DbModel implements TaggedInterface
{
    use FullPublished;
    use Tagged;

    protected function requiredWiths(): array
    {
        return [
            $this->tagLinksPropertyName
        ];
    }
}
