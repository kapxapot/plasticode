<?php

namespace Plasticode\Util;

class Pluralizer
{
    private static $immutable = [
        'news',
    ];

    public static function plural($word)
    {
        if (
            in_array(
                mb_strtolower($word),
                self::$immutable
            )
            || Strings::endsWith($word, 'ies')
        ) {
            return $word;
        }

        if (Strings::last($word) == 'y') {
            $word = substr_replace($word, 'ie', -1, 1);
        } elseif (Strings::last($word) == 's') {
            $word . 'e';
        }

        return $word . 's';
    }
}
