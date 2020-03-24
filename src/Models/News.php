<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\TagsInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tags;

class News extends DbModel implements TagsInterface
{
    use FullPublished, Tags;
}
