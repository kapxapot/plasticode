<?php

namespace Plasticode\Auth;

use Plasticode\Contained;
use Plasticode\Core\Security;
use Plasticode\Data\Tables;
use Plasticode\Exceptions\AuthenticationException;
use Plasticode\Util\Date;

class Auth extends Contained {
	private $user;
	private $role;
	private $token;
	
	private function setUserId($id) {
		$this->session->set('user', $id);
		$this->user = null;
	}

	private function setUser($user) {
		$this->setUserId($user->id);
	}
	
	private function setTokenId($id) {
		$this->session->set('token', $id);
		$this->token = null;
	}
	
	private function setToken($token) {
		$this->setUserId($token->user_id);
		$this->setTokenId($token->id);
	}
	
	private function login($token) {
		$this->setToken($token);
	}
	
	public function getUser() {
		if (!$this->user) {
			$id = $this->session->get('user');

			if ($id != null) {
				$user = $this->db->getObj(Tables::USERS, $id);
				/*if (empty($user->name)) {
					$user->name = $user->login;
				}*/
			
				$this->user = $user;
			}
		}
		
		return $this->user;
	}
	
	public function getRole() {
		if (!$this->role) {
			$user = $this->getUser();
			if ($user) {
				$id = $user->role_id;
				$this->role = $this->db->getObj(Tables::ROLES, $id);
			}
		}
		
		return $this->role;
	}
	
	public function isEditor() {
		$role = $this->getRole();
		
		return ($role != null) && ($role->id == 1 || $role->id == 2);
	}
	
	public function getToken() {
		if (!$this->token) {
			$id = $this->session->get('token');

			if ($id != null) {
				$this->token = $this->db->getObj(Tables::AUTH_TOKENS, $id);
			}
		}
		
		return $this->token;
	}
	
	public function userString() {
		$user = $this->getUser();
		return ($user != null)
			? "[{$user->id}] {$user->name}"
			: null;
	}
	
	public function tokenString() {
		$token = $this->getToken();
		return ($token != null)
			? "{$token->token}, expires at {$token->expires_at}"
			: null;
	}

	public function check() {
		return $this->getUser();
	}
	
	public function attempt($login, $password) {
		$user = $this->db
			->forTable(Tables::USERS)
			->whereAnyIs([
                [ 'login' => $login ],
                [ 'email' => $login ],
            ])
			->findOne();
		
		$ok = false;

		if ($user) {
			if (Security::verifyPassword($password, $user->password)) {
				if (Security::rehashPasswordNeeded($user->password)) {
					$user->password = Security::encodePassword($password);
					$user->save();
				}
				
				$token = $this->db->forTable(Tables::AUTH_TOKENS)->create();
				$token->user_id = $user->id;
				$token->token = Security::generateToken();
				$token->expires_at = $this->generateExpirationTime();
				
				$token->save();

				$this->login($token);

				$ok = true;
			}
		}
		
		return $ok;
	}

	public function logout() {
		$this->session->delete('token');
		$this->session->delete('user');
	}
	
	private function generateExpirationTime() {
		$ttl = $this->getSettings('token_ttl');
		return Date::generateExpirationTime($ttl * 60);
	}
	
	public function validateCookie($tokenStr) {
		try {
			$this->validateToken($tokenStr);
		}
		catch (\Exception $ex) {
			// do nothing
		}
	}

	public function validateToken($tokenStr, $ignoreExpiration = false) {
		$token = $this->getToken();
		if (!$token || $token->token != $tokenStr) {
			$token = $this->db->forTable(Tables::AUTH_TOKENS)
				->where('token', $tokenStr)
				->findOne();
			
			if ($token == null) {
				throw new AuthenticationException('Incorrect security token.');
			}
			elseif (!$ignoreExpiration && strtotime($token['expires_at']) < time()) {
				throw new AuthenticationException('Security token expired.');
			}
		}
			
		$token->expires_at = $this->generateExpirationTime();
		
		$token->save();

		$this->setToken($token);

		return $token;
	}
	
	public function isOwnerOf($item) {
		$user = $this->getUser();
		return isset($item['created_by']) && ($user !== null) && ($item['created_by'] == $user->id);
	}
}
