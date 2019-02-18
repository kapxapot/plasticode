<?php

namespace Plasticode\Middleware;

class SlashMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
	    $uri = $request->getUri();
	    $path = $uri->getPath();
	    
	    if ($path != '/' && substr($path, -1) == '/') {
	        // permanently redirect paths with a trailing slash
	        // to their non-trailing counterpart
	        $uri = $uri->withPath(substr($path, 0, -1));
	        return $response->withRedirect((string)$uri, 301);
	    }
	
	    return $next($request, $response);
	}
}
