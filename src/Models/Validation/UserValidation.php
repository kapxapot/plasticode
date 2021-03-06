<?php

namespace Plasticode\Models\Validation;

use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ExtendableValidation;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class UserValidation extends ExtendableValidation
{
    private UserRepositoryInterface $userRepository;

    private bool $isPasswordOptional = false;

    public function __construct(
        ValidationRules $validationRules,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct($validationRules);

        $this->userRepository = $userRepository;
    }

    /**
     * @return $this
     */
    public function withOptionalPassword(bool $optional = true): self
    {
        $this->isPasswordOptional = $optional;

        return $this;
    }

    protected function getOwnRules(array $data, $id = null): array
    {
        return [
            'updated_at' => Validator::unchanged($this->userRepository, $id),
            'login' => $this->rule('login')->loginAvailable($this->userRepository, $id),
            'email' => $this->rule('url')->email()->emailAvailable($this->userRepository, $id),
            'password' => $this->rule('password', $this->isPasswordOptional),
        ];
    }
}
