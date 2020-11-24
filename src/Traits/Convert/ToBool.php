<?php

namespace Plasticode\Traits\Convert;

use Plasticode\Util\Convert;

trait ToBool
{
    protected static function toBool(?int $value) : bool
    {
        return Convert::fromBit($value);
    }
}
