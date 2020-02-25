<?php

namespace Plasticode\Models;

use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Tags;

class News extends DbModel
{
    use FullPublish, Tags;
}
