<?php

namespace Plasticode\Util;

use Webmozart\Assert\Assert;

class Convert
{
    /**
     * Converts bool value to bit (0/1).
     * 
     * true => 1
     * false => 0
     * null => 0
     */
    public static function toBit(?bool $value) : int
    {
        return ($value === true) ? 1 : 0;
    }

    /**
     * Converts bit value to bool.
     *
     * 1 => true
     * 0 => false
     * null => false
     * anything else => exception
     */
    public static function fromBit(?int $value) : bool
    {
        if (is_null($value)) {
            return false;
        }

        Assert::true(in_array($value, [0, 1]));

        return $value == 1;
    }
}
