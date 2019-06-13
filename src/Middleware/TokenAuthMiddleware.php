<?php

namespace Plasticode\Middleware;

use Plasticode\Exceptions\AuthenticationException;

class TokenAuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $tokenLine = $request->getHeaderLine('Authorization');
        $lineParts = explode(' ', $tokenLine);
        
        if (count($lineParts) < 2) {
            throw new AuthenticationException('Invalid authorization header format. Expected "Basic <token>".');
        }
        
        $token = $lineParts[1];

        if ($this->auth->validateToken($token)) {
            $response = $next($request, $response);
        }

        return $response;
    }
}
