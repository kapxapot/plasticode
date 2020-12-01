<?php

namespace Plasticode\Models\Validation;

use Plasticode\Validation\Validation;
use Respect\Validation\Validator;

class PasswordValidation extends Validation
{
    public function getRules(array $data, $id = null) : array
    {
        return [
            'password_old' => Validator::matchesPassword($data['password']),
            'password' => $this->rule('password'),
        ];
    }
}
