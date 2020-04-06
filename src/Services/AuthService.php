<?php

namespace Plasticode\Services;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Models\AuthToken;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Date;

class AuthService
{
    /**
     * Settings path for token time-to-live
     */
    private const TOKEN_TTL_PATH = 'token_ttl';

    /**
     * Default token time-to-live in hours
     */
    private const DEFAULT_TOKEN_TTL = 24;

    private AuthInterface $auth;
    private SettingsProviderInterface $settingsProvider;
    private AuthTokenRepositoryInterface $authTokenRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        AuthInterface $auth,
        SettingsProviderInterface $settingsProvider,
        AuthTokenRepositoryInterface $authTokenRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->auth = $auth;
        $this->settingsProvider = $settingsProvider;
        $this->authTokenRepository = $authTokenRepository;
        $this->userRepository = $userRepository;
    }

    private function getToken() : ?AuthToken
    {
        $token = $this->auth->getToken();

        if (is_null($token)) {
            $tokenId = $this->auth->getTokenId();

            if ($tokenId > 0) {
                $token = $this->authTokenRepository->get($tokenId);
                $this->auth->setToken($token);
            }
        }

        return $token;
    }

    /**
     * Check if there is current user authenticated.
     */
    public function check() : bool
    {
        $token = $this->getToken();

        return $token && !$token->isExpired();
    }

    /**
     * Attempt login with login and password.
     */
    public function attempt(string $login, string $password) : ?AuthToken
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
        
        $token = $this->authTokenRepository->store(
            [
                'user_id' => $user->getId(),
                'token' => Security::generateToken(),
                'expires_at' => $this->generateExpirationTime(),
            ]
        );
        
        $this->auth->setToken($token);
        
        return $token;
    }

    /**
     * Logout the current user.
     */
    public function logout() : void
    {
        $this->auth->resetToken();
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
            
            if (!$ignoreExpiration && $token->isExpired()) {
                throw new AuthenticationException('Security token expired.');
            }
        }

        // renew token expiration
        $token->expiresAt = $this->generateExpirationTime();
        $token = $this->authTokenRepository->save($token);

        $this->auth->setToken($token);

        return $token;
    }
}
