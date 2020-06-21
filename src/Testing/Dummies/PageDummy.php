<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\Traits\FullPublished;

/**
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class PageDummy extends DbModel implements PageInterface
{
    use FullPublished;

    public function getSlug() : string
    {
        return $this->slug;
    }
}
