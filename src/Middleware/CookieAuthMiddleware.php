<?php

namespace Plasticode\Middleware;

use Plasticode\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class CookieAuthMiddleware
{
    /** @var Auth */
    private $auth;

    private $tokenKey = 'auth_token';
    
    public function __construct(Auth $auth, string $tokenKey = null)
    {
        $this->auth = $auth;

        if (strlen($tokenKey) > 0) {
            $this->tokenKey = $tokenKey;
        }
    }
    
    public function __invoke(
        Request $request,
        ResponseInterface $response,
        $next
    ) : ResponseInterface
    {
        $token = $request->getCookieParam($this->tokenKey);

        $this->auth->validateCookie($token);

        return $next($request, $response);
    }
}
