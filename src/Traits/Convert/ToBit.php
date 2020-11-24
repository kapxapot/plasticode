<?php

namespace Plasticode\Traits\Convert;

use Plasticode\Util\Convert;

trait ToBit
{
    protected static function toBit(?bool $value) : int
    {
        return Convert::toBit($value);
    }
}
