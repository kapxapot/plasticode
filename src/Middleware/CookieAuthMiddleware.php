<?php

namespace Plasticode\Middleware;

class CookieAuthMiddleware extends Middleware
{
    private $tokenKey = 'auth_token';
    
    public function __construct($container, $tokenKey = null)
    {
        parent::__construct($container);

        if (strlen($tokenKey) > 0) {
            $this->tokenKey = $tokenKey;
        }
    }
    
    public function __invoke($request, $response, $next)
    {
        $token = $request->getCookieParam($this->tokenKey);

        $this->auth->validateCookie($token);
        
        return $next($request, $response);
    }
}
