<?php

namespace Plasticode\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class UnchangedException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Data already changed. Please reload page.'
        ]
    ];
}
