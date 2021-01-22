<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\TypedCollection;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;

class MappingProviderCollection extends TypedCollection
{
    protected static $class = MappingProviderInterface::class;
}
