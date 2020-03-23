<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\TagsInterface;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Tags;

class News extends DbModel implements TagsInterface
{
    use FullPublish, Tags;
}
