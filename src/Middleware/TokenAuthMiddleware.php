<?php

namespace Plasticode\Middleware;

use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TokenAuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $next
    ) : ResponseInterface
    {
        // check if already authenticated (by cookie?)
        if ($this->authService->check()) {
            return $next($request, $response);
        }

        $tokenLine = $request->getHeaderLine('Authorization');
        $lineParts = explode(' ', $tokenLine);
        
        if (count($lineParts) < 2) {
            throw new AuthenticationException(
                'Invalid authorization header format. Expected "Bearer <token>".'
            );
        }
        
        $token = $lineParts[1];

        if ($this->authService->validateToken($token)) {
            $response = $next($request, $response);
        }

        return $response;
    }
}
