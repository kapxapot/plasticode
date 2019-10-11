<?php

namespace Plasticode\Validation\Rules;

class EmailAvailable extends TableFieldAvailable
{
    public function __construct($table, $id = null)
    {
        parent::__construct($table, 'email', $id);
    }
}
