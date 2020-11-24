<?php

namespace Plasticode\Generators;

use Plasticode\Core\Security;
use Plasticode\Validation\Interfaces\ValidationInterface;
use Psr\Container\ContainerInterface;

class UsersGenerator extends EntityGenerator
{
    protected ValidationInterface $userValidation;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->userValidation = $container->userValidation;
    }

    public function getRules(array $data, $id = null) : array
    {
        return $this->userValidation->getRules($data, $id);
    }

    public function beforeSave(array $data, $id = null) : array
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
