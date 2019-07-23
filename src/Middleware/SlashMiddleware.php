<?php

namespace Plasticode\Middleware;

/**
 * Permanently redirects paths with a trailing slash to their non-trailing counterpart
 */
class SlashMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $uri->withPath(substr($path, 0, -1));
            return $response->withRedirect((string)$uri, 301);
        }
    
        return $next($request, $response);
    }
}
