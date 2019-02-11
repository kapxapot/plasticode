<?php

namespace Plasticode\Controllers\Auth;

use Respect\Validation\Validator as v;

use Plasticode\Controllers\Controller;
use Plasticode\Core\Core;
use Plasticode\Core\Security;
use Plasticode\Data\Tables;
use Plasticode\Exceptions\NotFoundException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Exceptions\AuthenticationException;

class AuthController extends Controller {
	public function postSignUp($request, $response) {
		$settings = $this->getSettings();
		
		$data = $request->getParsedBody();

		$userGen = $this->generatorResolver->resolveEntity(Tables::USERS);

		$rules = $userGen->getRules($data);
		$validation = $this->validator->validate($request, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}

		if (!$this->captcha->validate($data['captcha'])) {
			throw new AuthenticationException('Incorrect or expired captcha.');
		}
		else {
			unset($data['captcha']);
		}

		$user = $this->db->forTable(Tables::USERS)->create();
		$user->set($data);
		
		$password = $user->password;
		$user->password = Security::encodePassword($password);

		$user->save();

		// signing in
		$user = $this->auth->attempt($user->login, $password);
		
		$this->logger->info("User signed up: {$this->auth->userString()}");

		$token = $this->auth->getToken();
		$response = $response->withStatus(201);

		$msg = $this->translate('Registration successful.');

		$response = Core::json($response, [ 'token' => $token->token, 'message' => $msg ]);

		return $response;
	}

	public function postSignIn($request, $response) {
		$ok = $this->auth->attempt(
			$request->getParam('login'),
			$request->getParam('password')
		);
		
		if (!$ok) {
			throw new AuthenticationException('Incorrect user/password.');
		}
		else {
			$this->logger->info("User logged in: {$this->auth->userString()}");
		
			$token = $this->auth->getToken();

			$msg = $this->translate('Login successful.');

			$response = Core::json($response, [ 'token' => $token->token, 'message' => $msg ]);
		}

		return $response;
	}

	public function postSignOut($request, $response) {
		$this->auth->logout();
		
		return $response;
	}
}
