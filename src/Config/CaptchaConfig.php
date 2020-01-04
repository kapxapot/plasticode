<?php

namespace Plasticode\Config;

use Plasticode\Config\Interfaces\CaptchaConfigInterface;

class CaptchaConfig implements CaptchaConfigInterface
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
