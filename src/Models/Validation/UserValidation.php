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
        $login = $this->rule('login', $this->isLoginOptional);

        if (!$this->isLoginOptional && strlen($data['login'] ?? null) > 0) {
            $login = $login->loginAvailable($this->userRepository, $id);
        }

        $email = $this->rule('url', $this->isEmailOptional);

        if (!$this->isEmailOptional && strlen($data['email'] ?? null) > 0) {
            $email = $email->email()->emailAvailable($this->userRepository, $id);
        }

        return [
            'login' => $login,
            'email' => $email,
            'password' => $this->rule('password', $this->isPasswordOptional),
        ];
    }
}
