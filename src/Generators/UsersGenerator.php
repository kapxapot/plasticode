<?php

namespace Plasticode\Generators;

use Plasticode\Core\Security;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class UsersGenerator extends EntityGenerator
{
    /** @var UserRepositoryInterface */
    protected $userRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->userRepository = $container->userRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $table = $this->userRepository->getTable();
        
        $rules['login'] = $this->rule('login')->loginAvailable($table, $id);
        $rules['email'] = $this->rule('url')->email()->emailAvailable($table, $id);
        $rules['password'] = $this->rule('password', $id);
        
        return $rules;
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
