<?php

namespace Plasticode\Middleware;

use Plasticode\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieAuthMiddleware
{
    private AuthService $authService;
    private string $tokenKey;

    public function __construct(AuthService $authService, string $tokenKey = null)
    {
        $this->authService = $authService;
        $this->tokenKey = $tokenKey ?? 'auth_token';
    }
    
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $next
    ) : ResponseInterface
    {
        $token = $request->getCookieParams()[$this->tokenKey] ?? null;

        $this->authService->validateCookie($token);

        return $next($request, $response);
    }
}
