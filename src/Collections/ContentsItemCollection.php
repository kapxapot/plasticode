<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\TypedCollection;
use Plasticode\Parsing\ContentsItem;

class ContentsItemCollection extends TypedCollection
{
    protected string $class = ContentsItem::class;
}
