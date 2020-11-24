<?php

namespace Plasticode\Traits\Convert;

use Plasticode\Util\Date;

trait ToIso
{
    /**
     * Null => null!
     */
    protected static function toIso(?string $date) : ?string
    {
        return $date
            ? Date::iso($date)
            : null;
    }
}
