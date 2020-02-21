<?php

namespace Plasticode\Models;

use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Tags;

/**
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class Page extends DbModel
{
    use FullPublish, Tags;
}
