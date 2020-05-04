<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Models\Tag;

class TagCollection extends DbModelCollection
{
    protected string $class = Tag::class;
}
