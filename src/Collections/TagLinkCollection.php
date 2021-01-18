<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\TypedCollection;
use Plasticode\Models\TagLink;

class TagLinkCollection extends TypedCollection
{
    protected string $class = TagLink::class;
}
