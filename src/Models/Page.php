<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\TagsInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tags;

/**
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class Page extends DbModel implements TagsInterface
{
    use FullPublished;
    use Tags;

    protected function requiredWiths(): array
    {
        return ['tagLinks'];
    }
}
