<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Tags extends AbstractRule
{
    public function validate($input)
    {
        return strlen($input) == 0 || preg_match('/^[^\?#\+]+$/', $input);
    }
}
