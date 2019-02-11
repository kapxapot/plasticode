<?php

namespace Plasticode\Util;

class Pluralizer
{
    private static $immutable = [
        'news',
    ];
    
    public static function plural($word)
    {
        $append = true;
        
        $last = Strings::last($word);
        
        if ($last == 'y') {
            $word = substr_replace($word, 'ie', -1, 1);
        }
        elseif (in_array(mb_strtolower($word), self::$immutable)) {
            $append = false;
        }
        elseif (Strings::endsWith($word, 'ies')) {
            $append = false;
        }
        
        if ($append) {
            $word .= 's';
        }
        
        return $word;
    }
}
