<?php

namespace Plasticode\Auth;

use Plasticode\Contained;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;
use Plasticode\Util\Date;

class Auth extends Contained
{
    /**
     * Current user
     *
     * @var Plasticode\Models\User
     */
    private $user;

    /**
     * Current role
     *
     * @var Plasticode\Models\Role
     */
    private $role;

    /**
     * Current auth token
     *
     * @var Plasticode\Models\AuthToken
     */
    private $token;
    
    /**
     * Set current auth token
     *
     * @param Plasticode\Models\AuthToken $token
     * @return void
     */
    private function setToken(AuthToken $token)
    {
        $this->session->set('token_id', $token->id);
    }

    /**
     * Get current auth token
     *
     * @return Plasticode\Models\AuthToken|null
     */
    public function getToken() : ?AuthToken
    {
        if (!$this->token) {
            $id = $this->session->get('token_id');

            if ($id) {
                $this->token = $this->authTokenRepository->get($id);
            }
        }
        
        return $this->token;
    }
    
    /**
     * Get current user
     *
     * @return Plasticode\Models\User|null
     */
    public function getUser() : ?User
    {
        if (!$this->user) {
            $token = $this->getToken();

            if ($token) {
                $this->user = $token->user();
            }
        }
        
        return $this->user;
    }
    
    /**
     * Get current role
     *
     * @return Plasticode\Models\Role|null
     */
    public function getRole() : ?Role
    {
        if (!$this->role) {
            $user = $this->getUser();
            
            if ($user) {
                $this->role = $user->role();
            }
        }
        
        return $this->role;
    }

    /**
     * Check if there is current user authenticated
     *
     * @return boolean
     */
    public function check() : bool
    {
        return !is_null($this->getUser());
    }
    
    /**
     * Attempt login with login and password
     *
     * @param string $login
     * @param string $password
     * @return Plasticode\Models\User|null
     */
    public function attempt(string $login, string $password) : ?User
    {
        $user = $this->userRepository->getByLogin($login);

        if (!$user) {
            return null;
        }
        
        $passwordOk = Security::verifyPassword($password, $user->password);

        if (!$passwordOk) {
            return null;
        }
        
        if (Security::rehashPasswordNeeded($user->password)) {
            $user->password = Security::encodePassword($password);
            $user->save();
        }
        
        $token = $this->authTokenRepository->store(
            [
                'user_id' => $user->id,
                'token' => Security::generateToken(),
                'expires_at' => $this->generateExpirationTime(),
            ]
        );
        
        $this->setToken($token);
        
        return $user;
    }

    /**
     * Logout the current user
     *
     * @return void
     */
    public function logout()
    {
        $this->session->delete('token_id');
    }
    
    /**
     * Generate expiration time based on token_ttl setting
     *
     * @return string
     */
    private function generateExpirationTime() : string
    {
        $ttl = $this->getSettings('token_ttl');
        return Date::generateExpirationTime($ttl * 60);
    }
    
    /**
     * Tries to validate auth token taken from cookie
     *
     * @param string $tokenStr
     * @return void
     */
    public function validateCookie(string $tokenStr)
    {
        try {
            $this->validateToken($tokenStr);
        }
        catch (AuthenticationException $authEx) {
            // do nothing
        }
    }

    /**
     * Validates auth token and authenticates user, if it's ok
     *
     * @param string $tokenStr
     * @param boolean $ignoreExpiration
     * @return AuthToken
     */
    public function validateToken(string $tokenStr, bool $ignoreExpiration = false) : AuthToken
    {
        $token = $this->getToken();
        if (!$token || $token->token != $tokenStr) {
            $token = $this->authTokenRepository->getByToken($tokenStr);
            
            if ($token == null) {
                throw new AuthenticationException('Incorrect security token.');
            }
            elseif (!$ignoreExpiration && strtotime($token->expiresAt) < time()) {
                throw new AuthenticationException('Security token expired.');
            }
        }
            
        $token->expiresAt = $this->generateExpirationTime();
        $token->save();

        $this->setToken($token);

        return $token;
    }
}
