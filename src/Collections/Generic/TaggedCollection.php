<?php

namespace Plasticode\Collections\Generic;

use Plasticode\Models\Interfaces\TaggedInterface;

class TaggedCollection extends DbModelCollection
{
    protected string $class = TaggedInterface::class;
}
