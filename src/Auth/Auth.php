<?php

namespace Plasticode\Auth;

use Plasticode\Contained;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Util\Date;

class Auth extends Contained
{
    private $user;
    private $role;
    private $token;
    
    private function setToken($token)
    {
        $this->session->set('token_id', $token->id);
    }

    public function getToken()
    {
        if (!$this->token) {
            $id = $this->session->get('token_id');

            if ($id) {
                $this->token = $this->authTokenRepository->get($id);
            }
        }
        
        return $this->token;
    }
    
    public function getUser()
    {
        if (!$this->user) {
            $token = $this->getToken();

            if ($token) {
                $this->user = $token->user();
            }
        }
        
        return $this->user;
    }
    
    public function getRole()
    {
        if (!$this->role) {
            $user = $this->getUser();
            
            if ($user) {
                $this->role = $user->role();
            }
        }
        
        return $this->role;
    }

    public function check()
    {
        return $this->getUser() !== null;
    }
    
    public function attempt($login, $password)
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

    public function logout()
    {
        $this->session->delete('token_id');
    }
    
    private function generateExpirationTime()
    {
        $ttl = $this->getSettings('token_ttl');
        return Date::generateExpirationTime($ttl * 60);
    }
    
    public function validateCookie($tokenStr)
    {
        try {
            $this->validateToken($tokenStr);
        }
        catch (\Exception $ex) {
            // do nothing
        }
    }

    public function validateToken($tokenStr, $ignoreExpiration = false)
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
    
    public function isOwnerOf($item)
    {
        $user = $this->getUser();
        return isset($item['created_by']) && ($user !== null) && ($item['created_by'] == $user->id);
    }
}
