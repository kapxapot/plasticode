<?php

namespace Plasticode\Controllers\Auth;

use Respect\Validation\Validator as v;

use Plasticode\Controllers\Controller;
use Plasticode\Core\Core;
use Plasticode\Core\Security;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationRules;

class PasswordController extends Controller {
	public function postChangePassword($request, $response) {
		$user = $this->auth->getUser();

		$data = [ 'password' => $user->password ];
		$rules = $this->getRules($data);
		$validation = $this->validator->validate($request, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}
		
		$password = $request->getParam('password');
		
		$user->password = Security::encodePassword($password);
		$user->save();
		
		$this->logger->info("Changed password for user: {$this->auth->userString()}");
		
		$msg = $this->translator->translateMessage('Password change successful.');

		$response = Core::json($response, [ 'message' => $msg ]);

		return $response;
	}
	
	private function getRules($data) {
		$rules = new ValidationRules($this->container);

		return [
			'password_old' => v::matchesPassword($data['password']),
			'password' => $rules->get('password'),
		];
	}
}
