<?php

namespace Plasticode\Auth;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Models\AuthToken;
use Plasticode\Models\Role;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Date;

class Auth
{
    /**
     * Settings path for token time-to-live
     * 
     * @var string
     */
    private const TokenTtlPath = 'token_ttl';
    
    /**
     * Default token time-to-live in hours
     * 
     * @var integer
     */
    private const DefaultTokenTtl = 24;

    /** @var SessionInterface */
    private $session;

    /** @var AuthTokenRepositoryInterface */
    private $authTokenRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /**
     * Current user
     *
     * @var User|null
     */
    private $user;

    /**
     * Current role
     *
     * @var Role|null
     */
    private $role;

    /**
     * Current auth token
     *
     * @var AuthToken|null
     */
    private $token;

    public function __construct(
        SessionInterface $session,
        SettingsProviderInterface $settingsProvider,
        AuthTokenRepositoryInterface $authTokenRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->session = $session;
        $this->settingsProvider = $settingsProvider;
        $this->authTokenRepository = $authTokenRepository;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Set current auth token.
     *
     * @param AuthToken $token
     * @return void
     */
    private function setToken(AuthToken $token) : void
    {
        $this->session->set('token_id', $token->id);
    }

    /**
     * Get current auth token.
     *
     * @return AuthToken|null
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
     * Get current user.
     *
     * @return User|null
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
     * Get current role.
     *
     * @return Role|null
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
     * Check if there is current user authenticated.
     *
     * @return boolean
     */
    public function check() : bool
    {
        return !is_null($this->getUser());
    }
    
    /**
     * Attempt login with login and password.
     *
     * @param string $login
     * @param string $password
     * @return User|null
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
            $user = $this->userRepository->save($user);
        }
        
        $token = $this->authTokenRepository->save(
            new AuthToken(
                [
                    'user_id' => $user->getId(),
                    'token' => Security::generateToken(),
                    'expires_at' => $this->generateExpirationTime(),
                ]
            )
        );
        
        $this->setToken($token);
        
        return $user;
    }

    /**
     * Logout the current user.
     *
     * @return void
     */
    public function logout() : void
    {
        $this->session->delete('token_id');
    }
    
    /**
     * Generate expiration time based on settings.
     *
     * @return string
     */
    private function generateExpirationTime() : string
    {
        $ttl = $this->settingsProvider->getSettings(
            self::TokenTtlPath,
            self::DefaultTokenTtl
        );
        
        return Date::generateExpirationTime($ttl * 60);
    }
    
    /**
     * Tries to validate auth token taken from cookie.
     *
     * @param string|null $tokenStr
     * @return void
     */
    public function validateCookie(?string $tokenStr) : void
    {
        try {
            $this->validateToken($tokenStr);
        }
        catch (AuthenticationException $authEx) {
            // do nothing
        }
    }

    /**
     * Validates auth token and authenticates user, if it's ok.
     *
     * @param string|null $tokenStr
     * @param boolean $ignoreExpiration
     * @return AuthToken
     */
    public function validateToken(?string $tokenStr, bool $ignoreExpiration = false) : AuthToken
    {
        $token = $this->getToken();

        if (!$token || $token->token != $tokenStr) {
            $token = $this->authTokenRepository->getByToken($tokenStr);
            
            if (is_null($token)) {
                throw new AuthenticationException('Incorrect security token.');
            }
            
            if (!$ignoreExpiration && strtotime($token->expiresAt) < time()) {
                throw new AuthenticationException('Security token expired.');
            }
        }

        $token->expiresAt = $this->generateExpirationTime();
        $token = $this->authTokenRepository->save($token);

        $this->setToken($token);

        return $token;
    }
}
