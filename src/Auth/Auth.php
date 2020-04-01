<?php

namespace Plasticode\Auth;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
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
     */
    private const TOKEN_TTL_PATH = 'token_ttl';

    /**
     * Default token time-to-live in hours
     */
    private const DEFAULT_TOKEN_TTL = 24;

    private SessionInterface $session;
    private AuthTokenRepositoryInterface $authTokenRepository;
    private UserRepositoryInterface $userRepository;
    private SettingsProviderInterface $settingsProvider;

    /**
     * Current auth token
     */
    private ?AuthToken $token = null;

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
     */
    private function setToken(AuthToken $token) : void
    {
        $this->token = $token;
        $this->session->set('token_id', $token->id);
    }

    /**
     * Resets (deletes) current auth token.
     */
    private function resetToken() : void
    {
        $this->token = null;
        $this->session->delete('token_id');
    }

    /**
     * Get current auth token.
     */
    public function getToken() : ?AuthToken
    {
        if (is_null($this->token)) {
            $id = $this->session->get('token_id');

            if ($id > 0) {
                $this->token = $this->authTokenRepository->get($id);
            }
        }
        
        return $this->token;
    }

    /**
     * Get current user.
     */
    public function getUser() : ?User
    {
        return $this->getToken()
            ? $this->getToken()->user()
            : null;
    }

    /**
     * Get current role.
     */
    public function getRole() : ?Role
    {
        return $this->getUser()
            ? $this->getUser()->role()
            : null;
    }

    /**
     * Check if there is current user authenticated.
     */
    public function check() : bool
    {
        return !is_null($this->getUser());
    }

    /**
     * Attempt login with login and password.
     */
    public function attempt(string $login, string $password) : ?User
    {
        $user = $this->userRepository->getByLogin($login);

        if (is_null($user)) {
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
     */
    public function logout() : void
    {
        $this->resetToken();
    }

    /**
     * Generate expiration time based on settings.
     */
    private function generateExpirationTime() : string
    {
        $ttl = $this->settingsProvider->get(
            self::TOKEN_TTL_PATH,
            self::DEFAULT_TOKEN_TTL
        );
        
        return Date::generateExpirationTime($ttl * 60);
    }

    /**
     * Tries to validate auth token taken from cookie.
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
     */
    public function validateToken(
        ?string $tokenStr,
        bool $ignoreExpiration = false
    ) : AuthToken
    {
        $token = $this->getToken();

        if (is_null($token) || $token->token != $tokenStr) {
            $token = $this->authTokenRepository->getByToken($tokenStr);
            
            if (is_null($token)) {
                throw new AuthenticationException('Incorrect security token.');
            }
            
            if (!$ignoreExpiration && strtotime($token->expiresAt) < time()) {
                throw new AuthenticationException('Security token expired.');
            }
        }

        // renew token expiration
        $token->expiresAt = $this->generateExpirationTime();
        $token = $this->authTokenRepository->save($token);

        $this->setToken($token);

        return $token;
    }
}
