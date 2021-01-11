<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\Tag;

class TagCollection extends DbModelCollection
{
    protected string $class = Tag::class;
}
