<?php

namespace Plasticode\Generators;

use Plasticode\Core\Security;

class UsersGenerator extends EntityGenerator
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);
        
        $table = $this->userRepository->getTable();
        
        $rules['login'] = $this->rule('login')->loginAvailable($table, $id);
        $rules['email'] = $this->rule('url')->email()->emailAvailable($table, $id);
        $rules['password'] = $this->rule('password', $id);
        
        return $rules;
    }
    
    public function beforeSave($data, $id = null)
    {
        $data = parent::beforeSave($data, $id);

        if (array_key_exists('password', $data)) {
            $password = $data['password'];
            if (strlen($password) > 0) {
                $data['password'] = Security::encodePassword($password);
            }
            else {
                unset($data['password']);
            }
        }

        return $data;
    }
}
