<?php

namespace Plasticode\Models\Validation;

use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ExtendableValidation;
use Plasticode\Validation\ValidationRules;

class UserValidation extends ExtendableValidation
{
    private UserRepositoryInterface $userRepository;

    private bool $isLoginOptional = false;
    private bool $isEmailOptional = false;
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
    public function withOptionalLogin(bool $optional = true): self
    {
        $this->isLoginOptional = $optional;

        return $this;
    }

    /**
     * @return $this
     */
    public function withOptionalEmail(bool $optional = true): self
    {
        $this->isEmailOptional = $optional;

        return $this;
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
            'login' => $this
                ->rule('login', $this->isLoginOptional)
                ->loginAvailable($this->userRepository, $id),
            'email' => $this
                ->rule('url', $this->isEmailOptional)
                ->email()
                ->emailAvailable($this->userRepository, $id),
            'password' => $this
                ->rule('password', $this->isPasswordOptional),
        ];
    }
}
