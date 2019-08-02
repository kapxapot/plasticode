<?php

namespace Plasticode\Middleware;

use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TokenAuthMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next) : ResponseInterface
    {
        $tokenLine = $request->getHeaderLine('Authorization');
        $lineParts = explode(' ', $tokenLine);
        
        if (count($lineParts) < 2) {
            throw new AuthenticationException('Invalid authorization header format. Expected "Bearer <token>".');
        }
        
        $token = $lineParts[1];

        if ($this->auth->validateToken($token)) {
            $response = $next($request, $response);
        }

        return $response;
    }
}
