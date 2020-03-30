<?php

namespace Plasticode\Models\Validation;

use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class UserValidation extends Validation
{
    /** @var UserRepositoryInterface */
    private $userRepository;

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
        $table = $this->userRepository->getTable();

        return [
            'updated_at' => Validator::unchanged($table, $id),
            'login' => $this->rule('login')->loginAvailable($table, $id),
            'email' => $this->rule('url')->email()->emailAvailable($table, $id),
            'password' => $this->rule('password', $id),
        ];
    }
}