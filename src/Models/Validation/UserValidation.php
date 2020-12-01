<?php

namespace Plasticode\Models\Validation;

use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\Validation;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class UserValidation extends Validation
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ValidationRules $validationRules,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct($validationRules);

        $this->userRepository = $userRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        return [
            'updated_at' => Validator::unchanged($this->userRepository, $id),
            'login' => $this->rule('login')->loginAvailable($this->userRepository, $id),
            'email' => $this->rule('url')->email()->emailAvailable($this->userRepository, $id),
            'password' => $this->rule('password'),
        ];
    }
}
