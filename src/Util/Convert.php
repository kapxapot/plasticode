<?php

namespace Plasticode\Util;

class Convert
{
    /**
     * Converts bool value to bit (0/1).
     * 
     * true => 1
     * false => 0
     * null => 0
     *
     * @param null|bool $value
     * @return int
     */
    public static function toBit(?bool $value) : int
    {
        return ($value === true) ? 1 : 0;
    }
}
