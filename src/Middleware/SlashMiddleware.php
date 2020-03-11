<?php

namespace Plasticode\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

/**
 * Permanently redirects paths with a trailing slash to their non-trailing counterpart
 */
class SlashMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        Response $response,
        $next
    ) : ResponseInterface
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
