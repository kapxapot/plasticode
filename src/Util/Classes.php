<?php

namespace Plasticode\Util;

class Classes
{
    public static function shortName(string $class)
    {
        return Strings::lastChunk($class, '\\');
    }
}
