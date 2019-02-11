<?php

namespace Plasticode\Util;

class Pluralizer
{
    public static function plural($word)
    {
        $last = Strings::last($word);
        
        if ($last == 'y') {
            $word = substr_replace($word, 'ie', -1, 1);
        }
        
        return $word . 's';
    }
}
