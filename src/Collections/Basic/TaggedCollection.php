<?php

namespace Plasticode\Collections\Basic;

use Plasticode\Models\Interfaces\TaggedInterface;

class TaggedCollection extends DbModelCollection
{
    protected string $class = TaggedInterface::class;
}
