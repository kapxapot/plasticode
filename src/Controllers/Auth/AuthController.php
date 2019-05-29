<?php

namespace Plasticode\Controllers\Auth;

use Respect\Validation\Validator as v;

use Plasticode\Controllers\Controller;
use Plasticode\Core\Core;
use Plasticode\Core\Security;
use Plasticode\Exceptions\NotFoundException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Exceptions\AuthenticationException;

class AuthController extends Controller {
	public function postSignUp($request, $response) {
		$settings = $this->getSettings();
		
		$data = $request->getParsedBody();

		$rules = $this->userRepository->getRules($data);
		$validation = $this->validator->validateRequest($request, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}

		if (!$this->captcha->validate($data['captcha'])) {
			throw new AuthenticationException('Incorrect or expired captcha.');
		}

		unset($data['captcha']);

		$user = $this->userRepository->create($data);

		$password = $user->password;
		$user->password = Security::encodePassword($password);
		$user->save();

		// signing in
		$user = $this->auth->attempt($user->login, $password);
		
		$this->logger->info("User signed up: {$user}");

		$token = $this->auth->getToken();
		$response = $response->withStatus(201);

		$response = Core::json($response, [
		    'token' => $token->token,
		    'message' => $this->translate('Registration successful.'),
		]);

		return $response;
	}

	public function postSignIn($request, $response) {
		$user = $this->auth->attempt(
			$request->getParam('login'),
			$request->getParam('password')
		);
		
		if (!$user) {
			throw new AuthenticationException('Incorrect user/password.');
		}

		$this->logger->info("User logged in: {$user}");
	
		$token = $this->auth->getToken();

		return Core::json($response, [
		    'token' => $token->token,
		    'message' => $this->translate('Login successful.'),
		]);
	}

	public function postSignOut($request, $response) {
		$this->auth->logout();
		
		return $response;
	}
}
