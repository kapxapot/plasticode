<?php

namespace Plasticode\Config;

class Captcha
{
    public function getReplaces() : array
    {
        return [
            'а' => [ '4' ],
            'о' => [ '0' ],
            'я' => [ 'R' ],
            'и' => [ 'N' ],
        ];
    }
}
