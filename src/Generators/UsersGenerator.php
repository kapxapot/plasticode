<?php

namespace Plasticode\Generators;

class UsersGenerator extends EntityGenerator
{
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['login'] = $this->rule('login')->loginAvailable($id);
		$rules['email'] = $this->rule('url')->email()->emailAvailable($id);
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
