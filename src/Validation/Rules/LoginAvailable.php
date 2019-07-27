<?php

namespace Plasticode\Validation\Rules;

class LoginAvailable extends TableFieldAvailable
{
    public function __construct($table, $id = null)
    {
        parent::__construct($table, 'login', $id);
    }
}
