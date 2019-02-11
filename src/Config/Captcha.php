<?php

namespace Plasticode\Config;

class Captcha
{
    public function getReplaces()
    {
        return [
    		'а' => [ '4' ],
    		'о' => [ '0' ],
    		'е' => [ '3' ],
    		'я' => [ 'R' ],
    		'д' => [ '9' ],
    		'и' => [ 'N' ],
    	];
    }
}
