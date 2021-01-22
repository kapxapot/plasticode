<?php

namespace Plasticode\Generators;

use Plasticode\Core\Security;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\Interfaces\ValidationInterface;

class UserGenerator extends ChangingEntityGenerator
{
    private UserRepositoryInterface $userRepository;
    private ValidationInterface $userValidation;

    public function __construct(
        GeneratorContext $context,
        UserRepositoryInterface $userRepository,
        ValidationInterface $userValidation
    )
    {
        parent::__construct($context);

        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }

    public function getRules(array $data, $id = null): array
    {
        return array_merge(
            parent::getRules($data, $id),
            $this->userValidation->getRules($data, $id)
        );
    }

    public function beforeSave(array $data, $id = null): array
    {
        $data = parent::beforeSave($data, $id);

        if (array_key_exists('password', $data)) {
            $password = $data['password'];

            if (strlen($password) > 0) {
                $data['password'] = Security::encodePassword($password);
            } else {
                unset($data['password']);
            }
        }

        return $data;
    }
}
