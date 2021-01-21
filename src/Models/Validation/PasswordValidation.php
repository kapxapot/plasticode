<?php

namespace Plasticode\Models\Validation;

use Plasticode\Validation\ExtendableValidation;
use Respect\Validation\Validator;

class PasswordValidation extends ExtendableValidation
{
    protected function getOwnRules(array $data, $id = null): array
    {
        return [
            'password_old' => Validator::matchesPassword($data['password']),
            'password' => $this->rule('password'),
        ];
    }
}
